<?php

namespace App\Services;

class AccrualService
{
    private $connection;
    private $host;
    private $username;
    private $password;

    public function __construct()
    {
        $this->host = env('ACCRUAL_IP');
        $this->username = env('ACCRUAL_USERNAME', 'r1_web');
        $this->password = env('ACCRUAL_PASSWORD', 'RA5bgdGc');
    }

    /**
     * Connect to FTP server
     *
     * @return bool
     */
    public function connect()
    {
        $this->connection = ftp_connect($this->host);
        
        if (!$this->connection || !@ftp_login($this->connection, $this->username, $this->password)) {
            return false;
        }
        
        return true;
    }

    /**
     * Upload file to FTP
     *
     * @param string $localFile
     * @param string $remoteFile
     * @return bool
     */
    public function uploadFile($localFile, $remoteFile)
    {
        if (!$this->connection) {
            if (!$this->connect()) {
                return false;
            }
        }

        $result = ftp_put($this->connection, $remoteFile, $localFile, FTP_BINARY);
        return $result;
    }

    /**
     * Close FTP connection
     *
     * @return void
     */
    public function disconnect()
    {
        if ($this->connection) {
            ftp_close($this->connection);
            $this->connection = null;
        }
    }

    /**
     * Process order data for Accrual
     *
     * @param array $requestData
     * @return array
     */
    public function processOrderData($requestData)
    {
        $location = $requestData['info']['location'];
        $locationName = '';
        $locationPrefix = '';
        
        switch ($location) {
            case 'URS':
                $locationName = 'Noliktava';
                $locationPrefix = 'U';
                break;
            case 'KRS':
                $locationName = 'Veikals';
                $locationPrefix = 'K';
                break;
        }
        
        $summa = $requestData['info']['total'];
        $summaPvn = number_format((float)($summa - ($summa / 1.21)), 2, '.', '');
        
        $articles = explode('$', $requestData['info']['article']);
        $qtyArray = $requestData['info']['qty'];
        $priceArray = $requestData['info']['price'];
        
        $items = [];
        
        if (is_array($articles) && (count($articles) > 1 || is_array($qtyArray) || is_array($priceArray))) {
            for ($i = 0; $i < count($articles); $i++) {
                $items[$i] = [
                    $articles[$i],
                    $qtyArray[$i],
                    $priceArray[$i]
                ];
            }
        } else {
            $items[0] = [
                $articles[0],
                $qtyArray,
                $priceArray
            ];
        }
        
        return [
            'location' => $location,
            'locationName' => $locationName,
            'locationPrefix' => $locationPrefix,
            'summa' => $summa,
            'summaPvn' => $summaPvn,
            'items' => $items
        ];
    }
} 