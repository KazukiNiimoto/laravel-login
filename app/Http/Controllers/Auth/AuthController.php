<?php

namespace App\Http\Controllers\Auth;


use App\Http\Controllers\Controller;
use App\Http\Requests\LoginFormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * @return view
     */
    public function showLogin(){
        return view('login.login_form');
    }

    /**
     * @param App\Http\Requests\LoginFormRequest
     * $request
     */
    public function login(LoginFormRequest $request){ // $requestがLoginFormRequest型だったら(タイプヒント) 
        $credentials = $request->only('email', 'password');//$requestからemailとpasswordだけを取得

        if(Auth::attempt($credentials)) { //ログインが成功したら
            $request->session()->regenerate(); //セッションIDの再設定

            // redirect('home')...home.blede.phpに直ちに再リクエストする。
            // with($key, $value)... セッションに保存
            return redirect()->route('home')->with('login_success', 'ログイン成功しました！');  
        }

        //attemptが失敗したら
        return back()->withErrors([ // 配列をセッションに保存
            'login_error' => 'メールアドレスかパスワードが間違っています。',
        ]);
    }

    /**
     * ユーザーをアプリケーションからログアウトさせる
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('showLogin')->with('logout', 'ログアウトしました！');
    }
}
