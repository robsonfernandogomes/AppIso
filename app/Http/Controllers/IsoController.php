<?php

namespace App\Http\Controllers;

use App\Models\Iso4217Item;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * @author Robson F. Gomes
 */
class IsoController extends Controller
{
    /**
     * @var string - Url no qual é informado a fonte externa no qual será feito o crawling para obter os dados falantes.
     */
    private string $urlToMakeCrawling = 'https://pt.wikipedia.org/wiki/ISO_4217';

    /**
     * Método responsável por fazer remoção das tags de <td> de uma derminada linha contida no campo $item
     * @param $item
     * @return string
     */
    private function removerTDTag($item): string
    {
        $item = str_replace("<td>", "", $item);
        $item = str_replace("</td>", "", $item);
        return $item;
    }

    /**
     * Método responsável por realizar a formatação do campo currency
     * @param $item
     * @return string
     */
    private function formatCurrencyData($item): string
    {
        $item = $this->removerTDTag($item);
        preg_match_all('~>.*<~', $item, $item);
        $item = str_replace(">", "", $item[0][0]);
        $item = str_replace("<", "", $item);
        return $item;
    }

    /**
     * Método responsável por realizar a formatação do campo de location.
     * @param $item
     * @return array
     */
    private function generateListLocationNames($item): array
    {
        preg_match_all('/title=\"([A-Za-z0-9 ]+?)\"/', $item, $arrayLocations);
        return $arrayLocations[1];
    }

    /**
     * Método responlsavel por fazer o crawling de uma pagina para buscar informações de um determinaodo item
     * através do parametro $isoCode
     * @param $isoCode
     * @return Iso4217Item|array
     */
    private function getDataByCrawling($isoCode): Iso4217Item|array
    {
        $isoCode = trim($isoCode);
        $isoCode = strtoupper($isoCode);
        $html = file_get_contents($this->urlToMakeCrawling);

        preg_match_all('~<td>.*~', $html, $matches);
        $index = array_search("<td>$isoCode</td>", $matches[0]);

        if ($index !== false) {
            $code = $this->removerTDTag($matches[0][$index]);
            $number = (int)$this->removerTDTag($matches[0][++$index]);
            $decimal = (int)$this->removerTDTag($matches[0][++$index]);
            $currency = $this->formatCurrencyData($matches[0][++$index]);
            $currency_location = $this->generateListLocationNames($this->removerTDTag($matches[0][++$index]));

            return new Iso4217Item($code, $number, $decimal, $currency, $currency_location);
        }
        return [];
    }

    /**
     * Mètodo responsável por converter uma string de códigos para um array.
     * @param $item
     * @return array
     */
    private function convertStringCodeToArrayCodes($item): array
    {
        $item = str_replace("[", "", $item);
        $item = str_replace("]", "", $item);
        $item = str_replace('"', "", $item);
        return explode(",", $item);
    }

    /**
     * Método responsável pela geração de um response através do parametro $response
     * @param $response
     * @param $code
     * @return JsonResponse
     */
    private function generateResponse($response, $code): JsonResponse
    {
        return new JsonResponse(json_encode($response), $code);
    }

    /**
     * Método responsável por fazer a busca de dados através de um código especifico.
     * @param $number
     * @return Collection
     */
    private function getCodeInDataBase($number): Collection
    {
        return DB::table(Iso4217Item::$entity)
            ->where('number', '=', $number)->get('code');
    }

    /**
     * Método responsável por fazer a busca de dados através de uma lista de códigos.
     * @param $request
     * @return JsonResponse
     */
    private function getIsoByCodeList($request): JsonResponse
    {
        $arrayCodes = $this->convertStringCodeToArrayCodes($request['code_list']);
        $arrayResponse = [];

        foreach ($arrayCodes as $code) {
            array_push($arrayResponse, $this->getDataByCrawling($code));
        }

        return $this->generateResponse($arrayResponse, 200);
    }

    /**
     * Método responsável por fazer a busca de dados através de um número especifico.
     * @param $request
     * @return JsonResponse
     */
    private function getIsoByNumber($request): JsonResponse
    {
        $code = $this->getCodeInDataBase($request['number']);

        if (sizeof($code) > 0) {
            return $this->generateResponse($this->getDataByCrawling($code[0]->code), 200);
        }
        return $this->generateResponse([], 200);
    }

    /**
     * Método responsável por fazer a busca de dados através de uma lista de números.
     * @param $request
     * @return JsonResponse
     */
    private function getIsoByNumberList($request): JsonResponse
    {
        $arrayNumbers = $this->convertStringCodeToArrayCodes($request['number_list']);
        $arrayResponse = [];

        foreach ($arrayNumbers as $number) {
            $code = $this->getCodeInDataBase($number);

            if (sizeof($code) > 0) {
                array_push($arrayResponse, $this->getDataByCrawling($code[0]->code));
            }
        }
        return $this->generateResponse($arrayResponse, 200);
    }

    /**
     * Mètodo responsável por fazer a busca por dados através dos parametro forneceido pelo $request
     * @param Request $request
     * @return Response|JsonResponse
     */
    public function getData(Request $request): Response|JsonResponse
    {
        if ($request->has('code')) {
            return $this->generateResponse($this->getDataByCrawling($request['code']), 200);
        }

        if ($request->has('code_list')) {
            return $this->getIsoByCodeList($request);
        }

        if ($request->has('number')) {
            return $this->getIsoByNumber($request);
        }

        if ($request->has('number_list')) {
            return $this->getIsoByNumberList($request);
        }
        return $this->generateResponse('Formato inválido', 400);
    }


}
