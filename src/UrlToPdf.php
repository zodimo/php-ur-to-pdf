<?php
/**
 * Created by IntelliJ IDEA.
 * User: jaco
 * Date: 2018/09/05
 * Time: 5:36 PM
 */

namespace Zodimo\PhpUrlToPdf;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class UrlToPdf
{
    const API_URL = "http://pdf:9000/api/render";

    private $output='pdf';
    private $ignoreHttpsErrors=false;
    private $emulateScreenMedia=true;
    private $scrollPage=false;

    private $client = null;
    private $url=null;
    private $html=null;
    private $page_options=array();
    private $page_options_default=array(
        'waitFor'=>null,
        'cookies'=>array(),
        'viewport'=>array(),
        'goto'=>array(),
        'pdf'=>array(),
        'screenshot'=>array(),
    );

    public function __construct()
    {
        $this->client = new Client();
    }

    public function setPdfOutput(){
        $this->output='pdf';
        return $this;
    }

    public function setScreenshotOutput(){
        $this->output='screenshot';
        return $this;
    }

    public function enableIgnoreHttpsErrors(){
        $this->ignoreHttpsErrors=true;
        return $this;
    }

    public function disableIgnoreHttpsErrors(){
        $this->ignoreHttpsErrors=false;
        return $this;
    }

    public function enableEmulateScreenMedia(){
        $this->emulateScreenMedia=true;
        return $this;
    }

    public function disableEmulateScreenMedia(){
        $this->emulateScreenMedia=false;
        return $this;
    }

    public function enableScrollPage(){
        $this->scrollPage=true;
        return $this;
    }

    public function disableScrollPage(){
        $this->scrollPage=false;
        return $this;
    }

    public function setUrl($value){
        $this->url=$value;
        return $this;
    }

    public function setHtml($value){
        $this->html=$value;
        return $this;
    }
    public function setPageOptions($options){
        $this->page_options=$options;
        return $this;
    }

    public function getPageOption($param){
        if(isset($this->page_options[$param]))
            return $this->page_options[$param];

        return $this->page_options_default[$param];
    }

    public function generate(){
        if(is_null($this->url) and is_null($this->html))
            throw new Exception('Url or Html must be set');

        if(!is_null($this->url)){
            $body['url']=$this->url;
            echo "url :" . $this->url."\n";
        }
        if(!is_null($this->html)){
            $body['html']=$this->html;
        }

        $body=self::merge(
            $body,
            array(
                'output'=>$this->output,
                'emulateScreenMedia'=>$this->emulateScreenMedia,
                'ignoreHttpsErrors'=>$this->ignoreHttpsErrors,
                'scrollPage'=>$this->scrollPage,
            )
        );
        if(!empty($this->getPageOption('waitFor')))
            $body=self::merge($body,['waitFor'=>$this->getPageOption('waitFor')]);

        if(!empty($this->getPageOption('cookies')))
            $body=self::merge($body,['cookies'=>$this->getPageOption('cookies')]);

        if(!empty($this->getPageOption('viewport')))
            $body=self::merge($body,['viewport'=>$this->getPageOption('viewport')]);

        if(!empty($this->getPageOption('goto')))
            $body=self::merge($body,['goto'=>$this->getPageOption('goto')]);

        if(!empty($this->getPageOption('pdf')))
            $body=self::merge($body,['pdf'=>$this->getPageOption('pdf')]);

        if(!empty($this->getPageOption('screenshot')))
            $body=self::merge($body,['screenshot'=>$this->getPageOption('screenshot')]);


        $response=$this->client->request('POST', self::API_URL, [
            RequestOptions::HEADERS=>['content-type' => 'application/json'   ],
            RequestOptions::BODY => json_encode($body,JSON_UNESCAPED_SLASHES)]);


        return $response->getBody()->getContents();
    }

    private static function merge($a, $b)
    {
        $res = $a;
        foreach ($b as $k => $v) {
            if (is_int($k)) {
                if (array_key_exists($k, $res)) {
                    $res[] = $v;
                } else {
                    $res[$k] = $v;
                }
            } elseif (is_array($v) && isset($res[$k]) && is_array($res[$k])) {
                $res[$k] = self::merge($res[$k], $v);
            } else {
                $res[$k] = $v;
            }
        }
        return $res;
    }
}