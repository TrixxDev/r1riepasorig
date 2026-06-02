<?php

  use App\Paysera\WebToPay;

  function isPaymentValid(array $order, array $response): bool
  {
    if (array_key_exists('payamount', $response) === false) {
      if ($order['amount'] !== $response['amount'] || $order['currency'] !== $response['currency']) {
        throw new Exception('Wrong payment amount');
      }
    } else {
      if ($order['amount'] !== $response['payamount'] || $order['currency'] !== $response['paycurrency']) {
        throw new Exception('Wrong payment amount');
      }
    }

    return true;
  }

  try {
    $response = WebToPay::validateAndParseData(
      $_REQUEST,
      '209872',
      'ef3e86e4902558e3779ecc84d72a6d8c'
    );

    if ($response['status'] === '1' || $response['status'] === '3') {
      //@ToDo: Validate payment amount and currency, example provided in isPaymentValid method.
      //@ToDo: Validate order status by $response['orderid']. If it is not already approved, approve it.

      echo 'OK';
    } else {
      throw new Exception('Payment was not successful');
    }
  } catch (Exception $exception) {
    echo get_class($exception) . ':' . $exception->getMessage();
  }
