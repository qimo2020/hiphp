<?php
namespace app;

use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\Handle;
use think\exception\HttpException;
use think\exception\HttpResponseException;
use think\exception\ValidateException;
use think\Response;
use Throwable;

/**
 * 应用异常处理类
 */
class ExceptionHandle extends Handle
{
    /**
     * 不需要记录信息（日志）的异常类列表
     * @var array
     */
    protected $ignoreReport = [
        HttpException::class,
        HttpResponseException::class,
        ModelNotFoundException::class,
        DataNotFoundException::class,
        ValidateException::class,
    ];

    /**
     * 记录异常信息（包括日志或者其它方式记录）
     *
     * @access public
     * @param  Throwable $exception
     * @return void
     */
    public function report(Throwable $exception): void
    {
        // 使用内置的方式记录异常日志
        parent::report($exception);
    }

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
        // 添加自定义异常处理机制
        if(!$this->app->isDebug()){
            $data['code'] = $e->getCode();
            if ($e instanceof HttpResponseException) {
                return $e->getResponse();
            }elseif ($e instanceof HttpException) { //http异常
                $data['msg'] = $e->getMessage();
                return $request->isAjax() ? Response::create($data, 'json', $e->getStatusCode())->header($e->getHeaders()) : Response::create(config('app.exception_tmpl'), 'view', $e->getStatusCode())->header($e->getHeaders())->assign($data);
            }else { //服务端内部异常或错误
                $data['msg'] = config('app.error_message');
                ob_start();
                extract($data);
                include '../app/system/exception/http.tpl';
                return Response::create(ob_get_clean())->code(501);
            }
        }else{
            // 其他错误交给系统处理
            return parent::render($request, $e);
        }
        
    }


}
