<?php
namespace App\Helpers;

class validateCard{

   public static function ValidCreditcards($number){
      $card_array = array(
         'default' => array(
            'length' => '13,14,15,16,17,18,19',
            'prefix' => '',
            'luhn' => TRUE,
         ),
         'american express' => array(
            'length' => '15',
            'prefix' => '34|37',
            'luhn' => TRUE,
         ),
         'diners club' => array(
            'length' => '14,16',
            'prefix' => '36|55|30[0-5]',
            'luhn' => TRUE,
         ),
         'discover' => array(
            'length' => '16',
            'prefix' => '6(?:5|011)',
            'luhn' => TRUE,
         ),
         'jcb' => array(
            'length' => '15,16',
            'prefix' => '3|1800|2131',
            'luhn' => TRUE,
         ),
         'maestro' => array(
            'length' => '16,18',
            'prefix' => '50(?:20|38)|6(?:304|759)',
            'luhn' => TRUE,
         ),
         'mastercard' => array(
            'length' => '16',
            'prefix' => '5[1-5]',
            'luhn' => TRUE,
         ),
         'visa' => array(
            'length' => '13,16',
            'prefix' => '4',
            'luhn' => TRUE,
         ),
      );

      if(($number = preg_replace('/\D+/', '', $number)) === ''){
         return FALSE;
      }

      $type = 'default';

      $cards = $card_array;

      $type = strtolower($type);

      if (!isset($cards[$type])){
         return FALSE;
      }

      $length = strlen($number);

      if (!in_array($length, preg_split('/\D+/', $cards[$type]['length']))){
         return FALSE;
      }

      if (!preg_match('/^' . $cards[$type]['prefix'] . '/', $number)){
         return FALSE;
      }

      if ($cards[$type]['luhn'] == FALSE){
         return $cards[$type]['luhn'];
      }

      $tipoTarjeta = preg_match('/^' . $cards['visa']['prefix'] . '/', $number) ? 'visa' : null;
      $tipoTarjeta = preg_match('/^' . $cards['mastercard']['prefix'] . '/', $number) ? 'mastercard' : $tipoTarjeta;
      $tipoTarjeta = preg_match('/^' . $cards['american express']['prefix'] . '/', $number) ? 'amex' : $tipoTarjeta;
      $tipoTarjeta = preg_match('/^' . $cards['discover']['prefix'] . '/', $number) ? 'discover' : $tipoTarjeta;
      $tipoTarjeta = preg_match('/^' . $cards['jcb']['prefix'] . '/', $number) ? 'jcb' : $tipoTarjeta;

      return $tipoTarjeta;
   }

    public static function check_cc($cc, $extra_check = false){
        $cards = array(
            "visa" => "/^4\d{12}(\d{3})?$/",
            "amex" => "/^3[47]\d{13}$/",
            "jcb" => "/^35[2-8][0-9]\d{12}$/",
            "maestro" => "/^(5020|5038|6304|6579|6761)\d{12}(\d{2,3})?$/",
            "mastercard" => "/^(5[1-5]\d{14}|2(22[1-9]|2[3-9]\d|[3-6]\d{2}|7([01]\d|20))\d{12})$/",
            "switch" => "/^(4903|4905|4911|4936|6333|6759)\d{12}(\d{2,3})?$|^(564182|633110)\d{10}(\d{2,3})?$/",
            "discover" => "/^6(011|5\d{2})\d{12}(\d{3})?$/"
        );

        $cc = str_replace(" ", "", $cc); // Eliminar espacios en blanco

        foreach ($cards as $type => $pattern) {
            if (preg_match($pattern, $cc)) {
                return $type;
            }
        }

        return false;

    }
}
