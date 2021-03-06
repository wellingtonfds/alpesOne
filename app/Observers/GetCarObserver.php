<?php


namespace App\Observers;


use App\Services\GetContent;
use App\Services\PersistContent;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Spatie\Crawler\CrawlObserver;
use Symfony\Component\ErrorHandler\Error\FatalError;

class GetCarObserver extends CrawlObserver
{
    public $brand = null;

    public function __construct($brand = null)
    {
        $this->brand = $brand;
    }

    /**
     * Exclude words
     */
    private $excludes = [
        'comparar','revendas','planos-de-anuncio','anuncie','faq','noticias','carro','quem-somos','publicidade',
        'politicas-de-privacidade','detrans','contato','termo-de-responsabilidade','/'
    ];

    /**
     * Check this uri is valid
     * @param String $uri
     * @return bool
     */
    private function validUri($uri){
        foreach ($this->excludes as $exclude){
            if(strpos($uri, $exclude)){
                return  false;
            }
        }
        return true;
    }


    /**
     * @inheritDoc
     */
    public function crawled(UriInterface $url, ResponseInterface $response, ?UriInterface $foundOnUrl = null)
    {
        if($url->getHost() == 'seminovos.com.br' &&  $url->__toString() != 'https://seminovos.com.br/'){
            if($this->validUri($url->getPath()) && $url->__toString() != null){

                try{
                    $getContent = new GetContent();
                    $content = $getContent->content($url->__toString());
                    $persistContent = new PersistContent();
                    $persistContent->persist($content);
                }catch (\Exception $e){
                    Log::error($e->getMessage()." line:".$e->getLine());
                }catch (FatalError $f){
                    Log::error($f->getMessage()." line:".$f->getLine());
                }
            }
        }

    }

    /**
     * @inheritDoc
     */
    public function crawlFailed(UriInterface $url, RequestException $requestException, ?UriInterface $foundOnUrl = null)
    {

    }

}
