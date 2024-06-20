<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use JsonSerializable;

class Iso4217Item extends Model implements JsonSerializable
{
    private $code;
    private $number;
    private $decimal;
    private $currency;
    private $currency_locations;

    /**
     * @param $code
     * @param $number
     * @param $decimal
     * @param $currency
     * @param $currency_locations
     */
    public function __construct($code, $number, $decimal, $currency, $currency_locations)
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

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $code
     */
    public function setCode($code): void
    {
        $this->code = $code;
    }

    /**
     * @return mixed
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param mixed $number
     */
    public function setNumber($number): void
    {
        $this->number = $number;
    }

    /**
     * @return mixed
     */
    public function getDecimal()
    {
        return $this->decimal;
    }

    /**
     * @param mixed $decimal
     */
    public function setDecimal($decimal): void
    {
        $this->decimal = $decimal;
    }

    /**
     * @return mixed
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param mixed $currency
     */
    public function setCurrency($currency): void
    {
        $this->currency = $currency;
    }

    /**
     * @return mixed
     */
    public function getCurrencyLocations()
    {
        return $this->currency_locations;
    }

    /**
     * @param mixed $currency_locations
     */
    public function setCurrencyLocations($currency_locations): void
    {
        $this->currency_locations = $currency_locations;
    }



}
