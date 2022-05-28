<?php

namespace App\WebScrapping;

/**
 * Scrapping WebScrapping
 * @link
 * @author Roberto Dorado <roberto.dorado@anexxa.com.br>
 * @package App\WebScrapping
 */
class Scrapping
{
    /**
     * url da requisição
     *
     * @var string $$url
     */
    private $url;

    /**
     * callback
     *
     * @var string
     */
    private $callback;

    /**
     * dom document
     *
     * @var mixed
     */
    private $dom_document;

    /**
     * dom xpath
     *
     * @var mixed
     */
    private $dom_xpath;

    /**
     * Scrapping constructor
     */
    public function __construct($url)
    {
        $this->url = $url;
    }

    /**
     * Request Url
     *
     * @return Scrapping
     */
    public function request()
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Cookie: Path=/',
            ),
        ));

        $response = curl_exec($curl);
        $error = curl_error($curl);
        
        curl_close($curl);

        if($error) {
            throw new \Exception('curl error');
        }else {
            $this->callback = $response;
        }

        if(preg_match("/error/i", $this->callback)) {

            preg_match("/\d+\serror/i", $this->callback, $this->callback);
            if(is_array($this->callback)) {
                $this->callback = implode('', $this->callback);
            }
        }

        return $this;
    }

    /**
     * retorna a callback
     *
     * @return string|null
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * retora a dom da página carregada
     *
     * @return Scrapping
     */
    public function loadDomDocument()
    {
        libxml_use_internal_errors(true);

        if(preg_match("/error/i", $this->callback)) {
            throw new \Exception('não é possivel carregar um documento com erro');
        }

        $this->dom_document = new \DomDocument();
        $this->dom_document->loadHTML($this->callback);

        return $this;
    }

    /**
     * retorna o objecto dom
     *
     * @return mixed
     */
    public function getDomDocument()
    {
        return $this->dom_document;
    }

    /**
     * Carregamento da DOM via XPath
     *
     * @param string $xpath_element
     * @return string
     */
    public function loadDomXPath($xpath_element)
    {
        if(empty($this->dom_document)) {
            throw new \Exception('dom document não foi carregado');
        }

        $this->dom_xpath = new \DOMXpath($this->dom_document);
        $node_elements = $this->dom_xpath->query($xpath_element);

        foreach($node_elements as $elements) {
            return utf8_decode($elements->textContent) . "\r";
        }
    }
}
