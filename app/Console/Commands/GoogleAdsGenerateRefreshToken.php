<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class GoogleAdsGenerateRefreshToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'google-ads:generate-refresh-token';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a Google Ads OAuth2 Refresh Token dynamically';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $clientId = config('marketing.google_ads.oauth_client_id') ?: $this->ask('Enter your OAuth2 Client ID');
        $clientSecret = config('marketing.google_ads.oauth_client_secret') ?: $this->ask('Enter your OAuth2 Client Secret');

        if (!$clientId || !$clientSecret) {
            $this->error('OAuth2 Client ID and Client Secret are required to proceed!');
            return self::FAILURE;
        }

        $this->info("Choose your Google Cloud Credential Type:");
        $this->info("1. Desktop Application (Recommended - easiest local flow)");
        $this->info("2. Web Application");
        $type = $this->choice('Credential Type', ['1', '2'], '1');

        if ($type === '1') {
            $redirectUri = 'http://localhost';
        } else {
            $redirectUri = $this->ask('Enter the Authorized Redirect URI configured in your Google Cloud Console', 'http://localhost');
        }

        $state = rand(10000, 99999);
        $authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'https://www.googleapis.com/auth/adwords',
            'access_type' => 'offline',
            'prompt' => 'consent',
            'state' => $state
        ]);

        $this->info("\n1. Open the following URL in your web browser to authorize the application:\n");
        $this->line($authUrl);
        $this->info("\n2. Log in with your Google Ads account and approve the permissions.");
        
        if ($type === '1') {
            $this->info("3. After approving, your browser will redirect to a page that may not load (e.g. 'http://localhost/?state=...&code=...').");
            $this->info("   Copy the entire 'code' parameter value from the browser's address bar.");
        } else {
            $this->info("3. Copy the 'code' parameter value from the redirected URL in the address bar.");
        }

        $code = $this->ask('Paste the authorization code here');

        if (empty($code)) {
            $this->error('Authorization code cannot be empty!');
            return self::FAILURE;
        }

        $this->info("Exchanging authorization code for an OAuth2 Refresh Token...");

        try {
            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'code' => trim($code),
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'redirect_uri' => $redirectUri,
                'grant_type' => 'authorization_code',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $refreshToken = $data['refresh_token'] ?? null;

                if ($refreshToken) {
                    $this->info("\n================ SUCCESS ================");
                    $this->info("Your Google Ads OAuth2 Refresh Token is:\n");
                    $this->line($refreshToken);
                    $this->info("\nAdd this token to your .env file as: GOOGLE_ADS_OAUTH_REFRESH_TOKEN");
                    $this->info("=========================================\n");
                    return self::SUCCESS;
                } else {
                    $this->error('Failed to retrieve refresh token. Raw response: ' . json_encode($data));
                }
            } else {
                $this->error('Token exchange failed! Error from Google: ' . $response->body());
            }
        } catch (\Throwable $e) {
            $this->error('An exception occurred during token exchange: ' . $e->getMessage());
        }

        return self::FAILURE;
    }
}
