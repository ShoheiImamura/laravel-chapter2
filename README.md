---

theme: "Night"
transition: "default"
progress: true
slideNumber: true
loop: true
backgroundTransition: 'zoom'


---

# 2章

### Laravel アーキテクチャ

---

## はじめに

- 本資料は、Weeyble Laravel 輪読会用の資料です
- 対応箇所は、2章の前半部分のみです
- 権利関係で問題がございましたら、ご指摘ください

--

## 発表者自己紹介

- 今村昌平と申します。
- Web業務システム受託の会社で勤務(1年半)
- 自分で Webアプリ作成中(マニュアル共有アプリ)
- Web業界入る前は、動物のお医者さん

--

## 本日の概要

### 前半

- Laravel のライフサイクル
- サービスコンテナ

### 後半

- サービスプロバイダ
- コントラクト

---

## 2-1 ライフサイクル

- HTTPリクエストを受けてから、HTTPレスポンスを返すまで
- 日本語リファレンス
  - [Laravel5.5 リクエストのライフサイクル](https://readouble.com/laravel/5.5/ja/lifecycle.html)

---

## 2-1-1 Laravelアプリケーションの実行の流れ

![Laravelアプリケーション実行の流れ](http://sample.com/images/sample.png)

--

### HTTPリクエストからアプリケーションの実行

1. HTTPリクエスト送信
2. public/index.php にてリクエストインスタンスを生成
3. HTTPカーネルにリクエストインスタンスを渡す
4. アプリケーションのセットアップ
5. ルータに　Request をディスパッチする


--

### アプリケーションの実行結果からHTTPレスポンスの出力

- リクエスト処理と逆の向きでレスポンスが返される

---

## 2-1-2 エントリポイント

- public/index.php が Laravel アプリケーションの起点となる
- ドキュメントルート配下に設置、Webサーバを設定する

--

### エントリポイントの全容

[エントリポイントへのリンク]()

--

### オートローダの読み込み

```php
define('LARAVEL_START', microtime(true));
require __DIR__.'/../vendor/autoload.php';

```

--

### フレームワークの起動

```php
$app = require_once __DIR__.'/../bootstrap/app.php';
```

--

### アプリケーションの実行

```php
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

```

--

### HTTPレスポンスの送信

```php
$response->send();
```

--

### 終了処理

```php
$kernel->terminate($request, $response);
```

---

## 2-1-3 HTTPカーネル

- アプリケーションのセットアップ
- ミドルウェアの設定
- ルータ実行
  - Request を与えて、Response を受け取る

--

### HTTPカーネルの実装

- エントリポイントから `handle` メソッドを実行
- デフォルトでは `App\Http\Kernel`クラスを実行
  - ミドルウェアの設定のみ記述
  - 実際の処理は `Illuminate\Foundation\Http\Kernel` クラスに実装

[App\Http\Kernel]()
[Illuminate\Foundation\Http\Kernel]()

--

### HTTPカーネルのhandleメソッド

- sendRequestThroughRouter() メソッドでアプリケーションを実行している
- 例外処理が発生した場合は、renderException()メソッドで Responseを生成

---

## 2-1-4 ルータ

- 定義されたルートから、Request にマッチするルートを探す
- マッチすると、そのコントローラ等を実行する
- `routes/web.php`(APIの場合は`routes/api.php`)に定義する

--

### ルートの定義

- ３種類の割当方法が存在する
  1. コントローラ名とメソッド名を指定する
  2. クラス名のみを指定する
  3. クロージャを指定する

--

### ルートの定義1

#### （コントローラ名とメソッド名を指定）

- 第2引数に`{コントローラ名}@{メソッド名}`で指定する

- 下記例は、URI が /tasks への GET リクエストに、TaskController クラス の getTasks メソッドを割当てる

    ```php
    Route::get('/tasks', 'TaskController@getTasks');
    ```

--

### ルートの定義2

#### （クラス名のみを指定）

- 第2引数に`{クラス名}`のみを指定する
- __invoke メソッドが存在すれば、そのメソッドを実行する

    ```php
    Route::post('/tasks', 'AddTaskAction');
    ```

--

### ルートの定義３3

#### （クロージャを指定）

- 第2引数に{クロージャ}を指定する
- 簡単な処理を実装する
- 実際のアプリケーションで使うケースは少ない

    ```php
    Route::get('/hello', function(Request $request){
        return view('hello);
    });
    ```

---

## 2-1-5 ミドルウェア

- RequestやResponseに対する処理を差し込むことができる
- 暗号化（複合）、セッション実行、認証処理等に利用される
- 複数のミドルウェアを組み合わせて用いる

--

### ミドルウェアの処理

- ミドルウェアとして `handle` メソッドが実行される
  - 第1引数：Request
  - 第2引数：次に実行するクロージャ

  ```php
  public function handle($request, Closure $next)
  {
      // ミドルウェアの処理
  }
  ```

--

### ミドルウェアの例(Cookie暗号化、複号)

#### 処理の流れ

1. Request に含まれる暗号化されたCookieを複合したRequestを取得
2. 次のミドルウェアを実行して、Responseを取得
3. ResponseのCookieを暗号化して返す

- [Illuminate\Cookie\Middleware\EncriptCookies]()

---

## 2-1-6 コントローラ

- HTTPリクエストに対応する処理を実行する
- ビジネスロジックの実行やデータベースアクセスなどの処理を実施する
- 処理後は Response を生成して返す
- 実務では、コントローラ内で処理が完結することは少ない

--

### コントローラの例

- 下記例では、サービスクラスを利用する

[TaskController]()

---

## 2-2 サービスコンテナ

---

## 2-2-1 サービスコンテナとは

---

## 2-2-2 バインドと解決

---

## 2-2-3 バインド

---

## 2-2-4 解決

---

## 2-2-5 DIとサービスコンテナ

---

## 2-2-6 ファサード

---

## 2章 前半の用語集

| No. | 日本語名         | 英語名        | 説明                                                                                 |
|-----|------------------|---------------|--------------------------------------------------------------------------------------|
| 1   | エントリポイント | entry point   | コンピュータプログラムを構成するコードのうち、最初に実行する事になっている位置のこと |
| 2   | HTTPリクエスト   | HTTP request  | Web ブラウザからからサーバに送信されるデータ送信要求                                 |
| 3   | HTTPレスポンス   | HTTP response | サーバが、HTTPリクエストに対して応答するデータ                                       |
| 4   | オートローダ     | autoloader    | クラスやインターフェイス、トレイトが定義されたPHPファイルを自動で読み込む仕組み      |

