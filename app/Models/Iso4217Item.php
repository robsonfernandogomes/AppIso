<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use JsonSerializable;

/**
 * @author Robson F. Gomes
 */
class Iso4217Item extends Model implements JsonSerializable
{
    public static string $entity ='tb_iso4217_items';
    private string $code;
    private int $number;
    private int $decimal;
    private string $currency;
    private array $currency_locations;

    /**
     * @param $code
     * @param $number
     * @param $decimal
     * @param $currency
     * @param $currency_locations
     */

    public function __construct($code = null, $number = null, $decimal= null, $currency= null, $currency_locations= null)
    {
        $this->code = $code;
        $this->number = $number;
        $this->decimal = $decimal;
        $this->currency = $currency;
        $this->currency_locations = $currency_locations;
    }

        public function jsonSerialize(): mixed {
        return [
            'code'=>$this->code,
            'number'=>$this->number,
            'decimal'=>$this->decimal,
            'currency'=>$this->currency,
            'currency_locations'=>$this->currency_locations,
            ];
        }
}
