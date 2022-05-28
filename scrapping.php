<?php

require_once __DIR__ . "/vendor/autoload.php";

$limit = 1;
$api = new \stdClass();

for($i = 1; $i<=$limit; $i++) {

    $query_string = "?limit=24&offset=" . 24 * $i . "";

    $url = "https://www.americanas.com.br/busca/celular{$query_string}";
    
    $web = (new \App\WebScrapping\Scrapping($url))->request()
        ->loadDomDocument()->loadDomXPath('//*[@id="rsyswpsdk"]/div/main/div/div[3]/div[2]');
    if(preg_match("/celular/i", $web)) {
        $web = preg_replace("/celular/i", "Smartphone", $web);
    }
    
    $products = explode("Smartphone", trim($web));
    
    $products = array_filter($products, function($value) {
        if(!preg_match("/^\s$/", $value)) {
            return $value;
        }
    });
    
    $products = array_values($products);
    
    foreach($products as $values) {
        $data = [];
        if(preg_match("/[avaliaçõesR$]+/", $values)) {
    
            $data['product'] = preg_replace("/(avaliações|avaliação).+$/", '', trim($values));
            $data['description'] = preg_replace("/^.+(avaliações|avaliação)/", '', trim($values));
    
            preg_match("/([R$\s\d]+\.\d+,\d{2}|[R$\s\d]+,\d{2})/", $data['description'], $value);

            if(empty($value)) {
                continue;
            }
        }
    
        if(preg_match("/%/", $values)) {
            $data['discount'] = explode('%', $data['description']);
            $percent_discount = preg_replace("/([R$\s\d]+\.\d+,\d{2}|[R$\s\d]+,\d{2})/", '', $data['discount'][0]);
            
            if(empty($data['discount'][1])) {
                continue;
            }

            preg_match("/([R$\s\d]+\.\d+,\d{2}|[R$\s\d]+,\d{2})/", $data['description'], $full_value);
            preg_match("/([R$\s\d]+\.\d+,\d{2}|[R$\s\d]+,\d{2})/", $data['discount'][1], $value_discount);

            if(empty($full_value)) {
                continue;
            }

            if(empty($value_discount)) {
                continue;
            }
    
            $data['discount']['full_value'] = $full_value[0];
            $data['discount']['value_discount'] = $value_discount[0];
            $data['discount']['percent_discount'] = $percent_discount;
    
            $data['discount'] = array_filter($data['discount'], function($value, $key) {
                if(gettype($key) != 'integer') {
                    return $value;
                }
            }, ARRAY_FILTER_USE_BOTH);
        }
    
        if(empty($data['discount'])) {
            $data['only_value'] = $value[0];

            if(!empty($data['description'])) {
                preg_match("/([R$\s\d]+\.\d+,\d{2}\d+|[R$\s\d]+,\d{2}\d+)/", $data['description'], $installments);
                preg_match("/([R$\s\d]+\.\d+,\d{2}\d+x\sde\s[R$\s\d]+\.\d+,\d{2}|[R$\s\d]+,\d{2}\d+x\sde\s[R$\s\d]+,\d{2})/", $data['description'], $installments_value);

                if(count($installments_value) > 1) {
                    array_shift($installments_value);
                    $installments_value = array_values($installments_value);
                }
    
                if(empty($installments_value)) {
                    continue;
                }
    
                if(empty($installments)) {
                    continue;
                }
    
                $installments = preg_replace("/([R$\s\d]+\.\d+,\d{2}|[R$\s\d]+,\d{2})/", '', $installments[0]);
                $installments_value = preg_replace("/([R$\s\d]+\.\d+,\d{2}|[R$\s\d]+,\d{2})\d+x\sde\s/", '', $installments_value[0]);
    
                $data['installments'] = $installments;
                $data['installments_value'] = $installments_value;
            }

        }
        
        $api->response[] = $data;
    }
}

print_r($api);