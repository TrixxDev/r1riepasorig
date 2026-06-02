<?php

namespace App\Helper;

class Env {

  public static function addVar(array $values): bool
  {

    $envFile = app()->environmentFilePath();
    $str = file_get_contents($envFile);

    if (count($values) > 0) {
      foreach ($values as $envKey => $envValue) {

        $envKey = strtoupper($envKey);

        $str .= "\n"; // In case the searched variable is in the last line without \n
        $keyPosition = strpos($str, "{$envKey}=");
        $endOfLinePosition = strpos($str, "\n", $keyPosition);
        $oldLine = substr($str, $keyPosition, $endOfLinePosition - $keyPosition);

        // If key does not exist, add it
        if (!$keyPosition || !$endOfLinePosition || !$oldLine) {
          if (is_integer($envValue)) {
            $str .= $envKey . '=' . $envValue;
          } else {
            $str .= $envKey . '="' . $envValue . '"';
          }
          $str .= "\n";
        } else {
          if (is_integer($envValue)) {
            $str = str_replace($oldLine, $envKey . '=' . $envValue, $str);
          } else {
            $str = str_replace($oldLine, $envKey . '="' . $envValue . '"', $str);
          }
        }

      }
    }

    $str = substr($str, 0, -1);
    if (!file_put_contents($envFile, $str)) return false;
    return true;

  }

  public static function removeVar(string $envKey): bool
  {

    $envFile = app()->environmentFilePath();
    $str = file_get_contents($envFile);

    if (str_word_count($envKey) > 0) {

      $envKey = strtoupper($envKey);

      $str .= "\n"; // In case the searched variable is in the last line without \n
      $keyPosition = strpos($str, "{$envKey}=");
      $endOfLinePosition = strpos($str, "\n", $keyPosition);
      $oldLine = substr($str, $keyPosition, $endOfLinePosition - $keyPosition);

      $str = rtrim(str_replace($oldLine, '', $str));

    }

//    $str = substr($str, 0, -1);
    if (!file_put_contents($envFile, $str)) return false;
    return true;

  }

}
