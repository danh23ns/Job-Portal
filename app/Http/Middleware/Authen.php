<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
class Authen
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->role == 'user') {
            return $next($request); // Cho phép người dùng có vai trò 'user'
        } else if (Auth::check() && Auth::user()->role == 'admin') {
            // Xử lý admin hoặc cho phép tiếp tục
            return $next($request); // Cho phép admin tiếp tục
        } else {
            // Nếu người dùng chưa đăng nhập, đăng xuất và chuyển hướng về trang đăng ký
            Auth::logout();
            return redirect()->route('account.registration');
        }
    }
}
