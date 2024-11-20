<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Kiểm tra xem người dùng đã đăng nhập chưa và kiểm tra xem vai trò của họ có phải là 'admin' hay không
        if (Auth::check() && Auth::user()->role == 'user') {
            // Nếu người dùng đã đăng nhập và vai trò là 'admin', cho phép tiếp tục yêu cầu (tiến vào route tiếp theo)
            return $next($request);
        } else {
            // Nếu người dùng chưa đăng nhập hoặc vai trò không phải là 'admin', đăng xuất người dùng
            Auth::logout();
            
            // Chuyển hướng người dùng về trang đăng ký
            return redirect()->route('account.registration');
        }
    }
}
