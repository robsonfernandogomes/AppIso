<?php

namespace App\Http\Controllers;

use App\Models\Iso4217Item;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class IsoController extends Controller
{
    private string $ISO_4217_DATA = 'https://pt.wikipedia.org/wiki/ISO_4217';

    private function removerTDTag($item) : string{
        $item = str_replace("<td>","",$item);
        $item = str_replace("</td>","",$item);
        return $item;
    }

    private function removerLink($item) : string{
        preg_match_all('~>.*<~', $item, $item);
        $item = str_replace(">","",$item[0][0]);
        $item = str_replace("<","",$item);

        return $item;
    }

    private function generateListLocationNames($item) : array{
        preg_match_all('/title=\"([A-Za-z0-9 ]+?)\"/', $item, $arrayLocations);
        return $arrayLocations[1];
    }

    private function crawlPage($url, $search): ?Iso4217Item
    {
            $html = file_get_contents($url);
            preg_match_all('~<td>.*~', $html, $matches);
            $index = array_search("<td>$search</td>",$matches[0]);

            if($index !== false){
                $code = $this->removerTDTag($matches[0][$index]);
                $number = (int)$this->removerTDTag($matches[0][++$index]);
                $decimal = (int)$this->removerTDTag($matches[0][++$index]);
                $currency = $this->removerTDTag($matches[0][++$index]);
                $currency = $this->removerLink($currency);
                $currency_location = $this->generateListLocationNames($this->removerTDTag($matches[0][++$index]));
                return new Iso4217Item($code, $number, $decimal, $currency, $currency_location);
            }
        return null;
    }
    private function verifyIsoByNumber(Request $request)
    {
    }
    private function verifyIsoByNumberList(Request $request)
    {
    }

    private function convertStringCodeToArray ($item) : array {
        $item = str_replace("[","",$item);
        $item = str_replace("]","",$item);
        $item = str_replace('"',"",$item);
        return explode(",",$item);
    }

    public function getISO4217Data(Request $request): \Illuminate\Http\Response|JsonResponse
    {
        if($request->has('code')){
            $item = $this->crawlPage($this->ISO_4217_DATA, $request['code']);
            return new JsonResponse(json_encode($item,),200);
        }

        if($request->has('code_list')){
            $arrayCodes = $this->convertStringCodeToArray($request['code_list']);
            $arrayResponse = [];

            foreach ($arrayCodes as $code){
                array_push($arrayResponse,$this->crawlPage($this->ISO_4217_DATA, $code));
            }
            return new JsonResponse(json_encode($arrayResponse),200);
        }

        if($request->has('number')){
            $this->verifyIsoByNumber($request);
        }

        if($request->has('number_list')){
            $this->verifyIsoByNumberList($request);
        }

        return Response::make('Formato inválido', 400);

    }


}
