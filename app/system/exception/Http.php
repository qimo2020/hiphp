<?php
namespace app\system\exception;
use think\exception\Handle;
use think\Response;
use Throwable;

/**
 * 应用异常处理类
 */
class Http extends Handle
{
    public $httpStatus = 501;
    public $httpHeaders = [];
    /**
     * Render an exception into an HTTP response.
     *
     * @access public
     * @param \think\Request   $request
     * @param Throwable $e
     * @return Response
     */
    public function render($request, Throwable $e): Response
    {
        if($this->app->isDebug()){
            return parent::render($request, $e);
        }else{
            $data = [
                'code' => $e->getCode(),
                'message' =>  $e->getMessage(),
            ];
            if(method_exists($e, "getStatusCode")){
                $this->httpStatus = $e->getStatusCode();
            }
            $this->httpHeaders = $e->getHeaders();
            return $request->isAjax() ? Response::create($data, 'json', $this->httpStatus)->header($this->httpHeaders) : Response::create(config('app.exception_tmpl'), 'view', $this->httpStatus)->header($this->httpHeaders)->assign($data);

        }
        
    }
}
