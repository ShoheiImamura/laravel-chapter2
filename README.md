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
- このスライドは reveal.js で閲覧することを前提に作成しています
  - 参考：[非エンジニアのためのお手軽reveal.js入門](https://jyun76.github.io/revealjs-vscode/)

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

[README.md](https://github.com/ShoheiImamura/laravel-chapter2#2-1-1-laravel%E3%82%A2%E3%83%97%E3%83%AA%E3%82%B1%E3%83%BC%E3%82%B7%E3%83%A7%E3%83%B3%E3%81%AE%E5%AE%9F%E8%A1%8C%E3%81%AE%E6%B5%81%E3%82%8C)
![IMG_20190725_002107494](https://user-images.githubusercontent.com/39234750/61806455-adfc2b00-ae72-11e9-959e-f6912af2e44a.jpg)

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
![エントリポイント](https://user-images.githubusercontent.com/39234750/61807514-a63d8600-ae74-11e9-9792-926d3ee13072.jpg)

--

### エントリポイントの全容

[/public/index.php](https://github.com/ShoheiImamura/laravel-chapter2/blob/master/sampleapp/public/index.php#L1)
![エントリポイント](https://user-images.githubusercontent.com/39234750/61807514-a63d8600-ae74-11e9-9792-926d3ee13072.jpg)


--

### オートローダの読み込み

```php
define('LARAVEL_START', microtime(true));
require __DIR__.'/../vendor/autoload.php';

// $app = require_once __DIR__.'/../bootstrap/app.php';
// $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
// $response = $kernel->handle(
//     $request = Illuminate\Http\Request::capture()
// );
// $response->send();
// $kernel->terminate($request, $response);
```

--

### フレームワークの起動

```php
// define('LARAVEL_START', microtime(true));
// require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

// $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
// $response = $kernel->handle(
//     $request = Illuminate\Http\Request::capture()
// );
// $response->send();
// $kernel->terminate($request, $response);
```

--

### アプリケーションの実行

```php
// define('LARAVEL_START', microtime(true));
// require __DIR__.'/../vendor/autoload.php';
// $app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// $response->send();
// $kernel->terminate($request, $response);
```

--

### HTTPレスポンスの送信

```php
// define('LARAVEL_START', microtime(true));
// require __DIR__.'/../vendor/autoload.php';
// $app = require_once __DIR__.'/../bootstrap/app.php';
// $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
// $response = $kernel->handle(
//     $request = Illuminate\Http\Request::capture()
// );

$response->send();

// $kernel->terminate($request, $response);
```

--

### 終了処理

```php
// define('LARAVEL_START', microtime(true));
// require __DIR__.'/../vendor/autoload.php';
// $app = require_once __DIR__.'/../bootstrap/app.php';
// $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
// $response = $kernel->handle(
//     $request = Illuminate\Http\Request::capture()
// );
// $response->send();

$kernel->terminate($request, $response);
```

---

## 2-1-3 HTTPカーネル

- アプリケーションのセットアップ
- ミドルウェアの設定
- ルータ実行
  - Request を与えて、Response を受け取る
![image](https://user-images.githubusercontent.com/39234750/61808871-3b417e80-ae77-11e9-9eaf-b186b9a5fa34.png)

--

### HTTPカーネルのソースコード確認

- エントリポイントから `handle` メソッドを実行
- デフォルトでは `App\Http\Kernel`クラスを実行
  - ミドルウェアの設定のみ記述
  - 実際の処理は `Illuminate\Foundation\Http\Kernel` クラスに実装

[App\Http\Kernel](https://github.com/ShoheiImamura/laravel-chapter2/blob/master/sampleapp/app/Http/Kernel.php#L1)
[Illuminate\Foundation\Http\Kernel](#)

--

### HTTPカーネルのhandleメソッド

- sendRequestThroughRouter() メソッドでアプリケーションを実行している
- 例外処理が発生した場合は、renderException()メソッドで Responseを生成

```php
public function handle($request){
    try {
        $request->enableHttpMethodParameterOverride();
        $response = $this->sendRequestThroughRouter($request); // 1 ルータを実行
    } catch (Exception $e) { // 2 例外発生時処理
        $this->reportException($e);
        $responcse = $this->renderException($request, $e);
    } catch (Throwable $e) { // 2' 例外発生時処理
        $this->reportException($request, $e);
        $response = $this->renderException($request, $e);
    }
    $this->app['events']->dispatch(
        new Events\RequestHandled($request, $response)
    );
}
```

---

## 2-1-4 ルータ

- 定義されたルートから、Request にマッチするルートを探す
- マッチした処理を実行する
- ルートの定義は `routes/web.php`（APIの場合は`routes/api.php`）に記載
![image](https://user-images.githubusercontent.com/39234750/61811155-d2103a00-ae7b-11e9-9389-ed3bb46ae5d3.png)

--

### ルートの定義

- 以下3種類の定義方法がある
  1. コントローラ名とメソッド名を指定する
  2. クラス名のみを指定する
  3. クロージャを指定する

  ```php
  // 1. コントローラ名とメソッド名を指定
  Route::get('/tasks', 'TaskController@getTasks');
  // 2. クラス名のみを指定
  Route::post('/tasks', 'AddTaskAction');
  // 3. クロージャをしてい
  Route::get('/hello', function(Request $request){
    return view('hello);
  });
  ```

--

### ルートの定義（コントローラ名とメソッド名を指定）

- 第2引数に`{コントローラ名}@{メソッド名}`で指定する

- 下記例は、URI が /tasks への GET リクエストに、TaskController クラス の getTasks メソッドを割当てる

    ```php
    Route::get('/tasks', 'TaskController@getTasks');
    ```

--

### ルートの定義（クラス名のみを指定）

- 第2引数に`{クラス名}`のみを指定する
- __invoke メソッドが存在すれば、そのメソッドを実行する

    ```php
    Route::post('/tasks', 'AddTaskAction');
    ```

--

### ルートの定義（クロージャを指定）

- 第2引数に`{クロージャ}`を指定する
- 簡単な処理を実装する場合に使う
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
- 複数のミドルウェアを組み合わせて用いることができる
![image](https://user-images.githubusercontent.com/39234750/61811411-64184280-ae7c-11e9-8060-ef3ac2c64af7.png)

--

### ミドルウェアの処理

- `handle` メソッド内に記述される
  - 第1引数：Request
  - 第2引数：次に実行するクロージャ
    - ミドルウェアがある場合は、ミドルウェア
    - そうでない場合は、コントローラ

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

[TaskController](https://github.com/ShoheiImamura/laravel-chapter2/blob/master/sampleapp/app/Http/Controllers/TaskController.php#L1-L22)

---

## 2-2 サービスコンテナ

### 日本語リファレンス

- [Laravel 5.5 サービスコンテナ
](https://readouble.com/laravel/5.5/ja/container.html)

---

## 2-2-1 サービスコンテナとは

- クラスのインスタンスの要求に対してインスタンスを生成して返す
- サービスコンテナの実態は Illuminate\Foundation\Aplication クラス
- サービスコンテナの機能は、継承元の Illuminate\Container\Container クラスに実装される

---

## 2-2-2 バインドと解決

- サービスコンテナに対して、インスタンスの生成方法を登録する処理を「バインド」よ呼ぶ
- 指定されたインスタンスをサービスコンテナが生成して返すことを「解決する」と呼ぶ

![image](https://user-images.githubusercontent.com/39234750/61812428-73988b00-ae7e-11e9-8ac6-348ff3c70121.png)

--

### サービスコンテナのインスタンス取得方法

- 3種類の方法を紹介
  1. app 関数から取得する
  2. Application::getInstanceメソッドから取得
  3. Appファサードから取得

--

### サービスコンテナのインスタンス取得方法例

```php
//   1. app 関数から取得する
$app = app();

//   2. Application::getInstanceメソッドから取得
$app = \Illuminate\Foundation\Application::getInstance();

//   3. Appファサードから取得
$app = \App::getInstance();
```

--

### バインドと解決の簡単な例

[README.md](https://github.com/ShoheiImamura/laravel-chapter2#%E3%83%90%E3%82%A4%E3%83%B3%E3%83%89%E3%81%A8%E8%A7%A3%E6%B1%BA%E3%81%AE%E7%B0%A1%E5%8D%98%E3%81%AA%E4%BE%8B)

```php
use Illuminate\Foundation\App\Application;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

class FooLogger
{
    protected $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}

// 「bind」メソッドを用いて、インスタンスの生成処理をクロージャで指定する(バインド)
app()->bind(FooLogger::class, function (Application $app){ 
    $logger = new Logger('my_log');
    return new FooLogger($logger);
});

// 「make」メソッドを用いて、クラスのインスタンスの生成を要求する(解決)
$foologger = app()->make(FooLogger::class);
```

---

## 2-2-3 バインド

- サービスコンテナに対して、インスタンスの生成方法を登録する処理を「バインド」よ呼ぶ
- バインド（サービスコンテナに対してインスタンスの生成方法を登録する）には下記の方法がある
  1. bind メソッド
  2. bindif メソッド
  3. singleton メソッド
  4. instance メソッド
  5. when メソッド

--

### bind メソッド

- 最も利用されるバインドメソッド
- 第1引数に文字列、第2引数にインスタンスの生成処理をクロージャで指定する
`app()->bind( {文字列} , {クロージャ} )`
- バインドしたクロージャは解決されるたびに実行される
  - クロージャに引数を設定し、解決時にパラメータを渡すこともできる

--

### bind メソッドの例

```php
// Number クラス
class Number
{
    protected $number;

    public function __construct($number = 0)
    {
        $this->number = $number;
    }

    public function getNumber()
    {
        return $this->number;
    }
}

// Number クラスが要求された場合の返却値を指定する
app()->bind(Number::class, function(){
    return new Number();
});

// Number クラスを要求する
$numcls = app()->make(Number::class);

// Number インスタンスのメソッドを実行する
$number= $numcls->getNumber();
```

--

### singleton メソッド

- 一度作成したインスタンスはキャッシュする
- 2回目以降の解決では、キャッシュされたインスタンスを返却する

--

### singleton メソッドの例

```php
// Number クラスを継承
class RandomNumber extends Number
{
    public function __construct()
    {
        // Number クラスのコンストラクタにランダムな値を返す
        parent::__construct(mt_rand(1,10000));
    }
}

app()->singleton('random', function () {
    return new RandomNumber();
});

$number1 = app('random');
$number2 = app('random');

// $number2 は新規に作成されず、キャッシュされたインスタンスが返されるため
// $number1->getNumber() === $number2->getNumber() となる
```

--

### Instance メソッド

- すでに生成したインスタンスをサービスコンテナにバインドする
- Singleton メソッドと同様にサービスコンテナにキャッシュされる

--

### Instance メソッドの例

- あらかじめ生成した number クラスのインスタンスをバインドする

```php
$numcls = new Number(1001);
app()->instance('SharedNumber', $numcls);

$number1 = app('ShareNumber');
$number2 = app('ShareNumber');

// $number1->getNumber() === $number2->getNumber()
```

--

### 別の文字列による解決処理のバインド

- 第2引数に文字列を指定すると、解決時に第2引数で指定する文字列を解決して返す
`$app->singleton({文字列}, {文字列})`

```php
// 別の文字列として解決する処理をバインドする例
$app->singleton(
    Illuminate\Constracts\Http\Kernel::class,
    App\Http\Kernel::class
);

// App\Http\Kernelというクラスめいにより解決される
$app = app(Illuminate\Constracts\Kernell::class);
```

--

### バインドの定義場所

- app\Providers フォルダに ServiceProvider クラスを作成して定義するのがオススメ
- 既に存在する AppServiceProviderクラス内に定義してもよい
- 通常バインド処理は register メソッドに記載する
  - インスタンス生成時に他のクラスを利用する場合は boot メソッド内に記載する

[AppServiceProviderクラス](https://github.com/ShoheiImamura/laravel-chapter2/blob/master/sampleapp/app/Providers/AppServiceProvider.php#L1-L28)

```shell
app
  |--Providers
     |--AppServiceProvider.php
     |--AuthServiceProvider.php
     |--BroadcastServiceProvider.php
     |--EventServiceProvider.php
     |--RouteServiceProvider.php

```

---

## 2-2-4 解決

- 指定されたインスタンスをサービスコンテナが生成して返すことを「解決する」と呼ぶ
- サービスコンテナが解決したインスタンスを取得する方法は以下2種類ある
  1. make メソッド
  2. app ヘルパ関数
- バインドしていない文字列も解決できる

--

### make メソッドによる解決

- make メソッドは引数に対象の文字列を指定する

```php
app()->bind(Number::class, function() {
    return new Number();
});

$number1 = app()->make(Number::class);
```

--

### app ヘルパ関数による解決

- makeメソッドと同じく、appヘルパ関数は引数に文字列を指定する

```php
app()->bind(Number::class, function() {
    return new Number();
});

$number2 = app(Number::class);
```

--

### バインドしていない文字列の解決

- サービスコンテナには、バインドしていない文字列を解決する機能も存在する
  - 条件：`解決する文字列がクラス名` && `具象クラス`
  - サービスコンテナがそのクラスのコンストラクタを実行してインスタンスを返す

--

### バインドしていない文字列の解決例

[Github README.md](https://github.com/ShoheiImamura/laravel-chapter2#%E3%83%90%E3%82%A4%E3%83%B3%E3%83%89%E3%81%97%E3%81%A6%E3%81%84%E3%81%AA%E3%81%84%E6%96%87%E5%AD%97%E5%88%97%E3%81%AE%E8%A7%A3%E6%B1%BA%E4%BE%8B)

```php
// 具象クラス
class Unbinding
{
    protected $name;

    public function __construct($name = '')
    {
        $this->name = $name;
    }
}

// 具象クラスのコードがあるため、自動でインスタンスが生成、返却される
$unbinding1 = app()->make(Unbinding::class);

// パラメータを指定して、コンストラクタにわたすこともできる
$unbinding2 = app()->make(Unbinding::class, ['Hoge']);
```

---

## 2-2-5 DIとサービスコンテナ

- クラスやメソッド内で利用する機能を外部から渡す設計パターンをDI(依存性の注入)と呼ぶ
- 依存性の注入には、以下2種類の方法がある
  1. コンストラクタインジェクション
  2. メソッドインジェクション

--

### クラスが別クラスと依存関係にあるコード

- `UserServiceクラス` 内で `MailSenderクラス` を直接指定し、インスタンスを生成する

[Github README.md](https://github.com/ShoheiImamura/laravel-chapter2#%E3%82%AF%E3%83%A9%E3%82%B9%E3%81%8C%E5%88%A5%E3%82%AF%E3%83%A9%E3%82%B9%E3%81%A8%E4%BE%9D%E5%AD%98%E9%96%A2%E4%BF%82%E3%81%AB%E3%81%82%E3%82%8B%E3%82%B3%E3%83%BC%E3%83%89)

```php
// 利用者にメール通知をするクラス
class UserService
{
    public function notice($to, $message)
    {
        $mailsender = new MailSender();
        $meilsenser->send($to, $message);
    }
}

class MailSender
{
    public function send($to, $message)
    {
        // send mail ...
    }
}
```

### クラスが別クラスと依存関係にないコード

- `UserServiceクラス` 内では `MailSenderクラス` を直接指定しない
- `サービスコンテナ`が、`引数内の文字列`と具象クラスの `MailSenderクラス` とをバインドし、MailSender のインスタンスを返却する

```php
class UserService
{
    public function notice(MailSender $mailsender, $to, $message)
    {
        $mailsender->send($to, $message);
    }
}
```

--

### DI(依存性の注入)

- クラスやメソッド内で利用する機能を外部から渡す設計パターンをDI（依存性の注入）という
- クラス同士が依存している状態よりも、クラスの差し替えの影響を制限できる
- 具象クラスではなく、抽象クラス（インターフェイス）に依存させることで、クラス間の依存関係を疎にすることができる

--

### 抽象クラス（インターフェイス）に依存したコード

[Github README.md](https://github.com/ShoheiImamura/laravel-chapter2#%E6%8A%BD%E8%B1%A1%E3%82%AF%E3%83%A9%E3%82%B9%E3%82%A4%E3%83%B3%E3%82%BF%E3%83%BC%E3%83%95%E3%82%A7%E3%82%A4%E3%82%B9%E3%81%AB%E4%BE%9D%E5%AD%98%E3%81%97%E3%81%9F%E3%82%B3%E3%83%BC%E3%83%89)

```php
class UserService
{
    // 引数に抽象クラスを指定する
    public function notice(NotifierInterface $notifier, $to, $message)
    {
        $notifier->send($to, $message);
    }
}

// 抽象クラス
interface NotifierInterface
{
    public function send($to, $message);
}

// 具象クラス(メール通知)
class MailSender implements NotifierInterface
{
    public function send($to, $message)
    {
        // send mail
    }
}

// 具象クラス(PUSH通知)
class PushSender implements NorifierInterface
{
    public function send($to, $message)
    {
        // send push notification
    }
}
```

--

### バインドの変更

- インターフェイスと具象クラスのバインドの設定を変更することで、
- クラス内のコードを変更せずに、クラス内で使用するインスタンスを変更することができる

```php
// メール送信でユーザ通知する場合
app()->bind(NotifierInterface::class, function(){
    return new MailSender();
})

// PUSH通知でユーザ通知する場合
app()->bind(NotifierInterface::class, function(){
    return new PushSender();
})
```

--

### コンストラクタインジェクション

- クラスのコンストラクタの引数でインスタンスを注入する方法
- コンストラクタ仮引数で、必要なクラスをタイプヒンティングで指定する

--

### コンストラクタインジェクションの例

[Github README.md](https://github.com/ShoheiImamura/laravel-chapter2#%E3%82%B3%E3%83%B3%E3%82%B9%E3%83%88%E3%83%A9%E3%82%AF%E3%82%BF%E3%82%A4%E3%83%B3%E3%82%B8%E3%82%A7%E3%82%AF%E3%82%B7%E3%83%A7%E3%83%B3%E3%81%AE%E4%BE%8B)

```php
class UserService
{
    protected $sender;

    public function __construct(NorifierInterface $notifier)
    {
        $this->notifier = $notifier;
    }

    public function notice($no, $message)
    {
        $this->notifier->send($to, $message);
    }
}

$user = app()->make(UserService::class);
$user->send('to', 'message');
```

--

### メソッドインジェクション

- メソッドの引数で必要とするインスタンスを渡す方法
- メソッドの仮引数を用いて、タイプヒンティングでクラスを指定する

--

### メソッドインジェクションの例

```php
class UserService
{
    public function notice(NotifierInterface $notifier, $to, $message)
    {
        $notifier->send($to, $message);
    }
}

$service = app(UserService::class);
app()->call([$service, 'notice'], ['to', 'message']);
```

--

### コンテキストに応じた解決

- 注入先のクラス名に応じて、異なる具象クラスのインスタンスを生成・返却することができる
- 「when」メソッドを用いてバインドを行う

| メソッド | 使い方                                     |
|----------|--------------------------------------------|
| when     | 引数に注入々のクラス名を指定する           |
| needs    | 対象のタイプヒンティングを指定する         |
| give     | サービスコンテナで解決する文字列を指定する |

--

### 異なるインスタンスを要求するクラスの例

- 2種類のクラスで同一名のインターフェイス（`NotifierInterface`）を指定している
- `UserService`クラスでは `PushSender` インスタンスが注入される
- `AdminService`クラスでは `MailSender` インスタンスが注入される

```php
class UserService
{
    protected $notifier;

    public function __construct(NotifierInterface $notifier)
    {
        $this->notifier = $notifier;
    }
}

class AdminService
{
    protected $notifier;

    public function __construct(NotifierInterface $notifier)
    {
        $this->notifer = $notifier;
    }
}

app()->when(UserService::class) // UserService クラスから
     ->needs(NotifierInterface::class) // NotifierInterface が指定されたら
     ->give(PushSender::class); // PushSender クラスのインスタンスを返す

app()->when(AdminService::class) // AdminService クラスから
     ->needs(NotifierInterface::class) // NotifierInterface が指定されたら
     ->give(MailSender::class); // MailSender クラスのインスタンスを返す
```

---

## 2-2-6 ファサード

- フレームワーク内のクラス、メソッドを`簡単に`利用できる機能
- 登録されている別名を指定して、ファサードを利用する
  - [config/app.php](https://github.com/ShoheiImamura/laravel-chapter2/blob/master/sampleapp/config/app.php#L195-L227) で aliase を登録している

| 別名   | クラスの実態                            |
|--------|-----------------------------------------|
| App    | Illuminate\Support\Facades\App:class    |
| Auth   | Illuminate\Support\Facades\Auth:class   |
| ...    | ...                                     |
| Config | Illuminate\Support\Facades\Config:class |
| ...    | ...                                     |

--

### ファサードの利用例

- config ファサードから値を取得する

```php
$debug = \Config::get('app.debug');
```

--

### Config ファサードの処理の流れ

1. Config::get('app.debug') がコールされる
2. Config の実体である Illuminate\Support\Facades\Config クラスの get メソッドを呼び出す
3. Illuminate\Support\Facades\Configクラスには get メソッドが無いため、スーパークラスの __callStatic メソッドを呼び出す。
4. __callStatic メソッドでは、getFacadeRootメソッドで操作対象のインスタンスを取得し、getメソッドを実行する（getFacadeRootメソッドで操作対象のインスタンスを取得した文字列を resolveFacadeInstanceメソッドによりサービスコンテナで解決し、取得したインスタンスを返す）

--

### ファサードの注意点

[日本語リファレンス Laravel 5.5 ファサード](https://readouble.com/laravel/5.5/ja/facades.html)
>ファサードの一番の危険性は、クラスの責任範囲の暴走です。ファサードはとても簡単に使用でき、依存注入も必要ないため、簡単にクラスが成長し続ける結果、一つのクラスで多くのファサードが使われます。依存注入を使用すれば、クラスが大きくなりすぎることに伴う、大きなコンストラクタの視覚的なフィードバックにより、この危険性は抑制されます。ですから、ファサードを使用するときは、クラスの責任範囲を小さくとどめるため、クラスサイズに特に注意をはらいましょう。

---

## Appendix

[README.md](https://github.com/ShoheiImamura/laravel-chapter2#appendix)

--

## 2章 前半の用語集

| No. | 日本語名         | 英語名               | 説明                                                                                 |
|-----|------------------|----------------------|--------------------------------------------------------------------------------------|
| 1   | エントリポイント | entry point          | コンピュータプログラムを構成するコードのうち、最初に実行する事になっている位置のこと |
| 2   | HTTPリクエスト   | HTTP request         | Web ブラウザからからサーバに送信されるデータ送信要求                                 |
| 3   | HTTPレスポンス   | HTTP response        | サーバが、HTTPリクエストに対して応答するデータ                                       |
| 4   | オートローダ     | autoloader           | クラスやインターフェイス、トレイトが定義されたPHPファイルを自動で読み込む仕組み      |
| 5   | サービスコンテナ | Service Container    | クラス間の依存を管理する強力な管理ツール                                             |
| 6   | バインド         | bind                 | サービスコンテナに対して、インスタンスの生成方法を登録する処理                       |
| 7   | 解決             | resolve              | 指定されたインスタンスをサービスコンテナが生成して返すこと                           |
| 8   | 依存性の解決     | Dependency Injection | クラスやメソッド内で利用する機能を外部から渡す設計パターン                           |

--

## Laradock による環境構築

### 任意のリポジトリで実施します

```shell
# 本資料 Github リポジトリを git clone します
git clone https://github.com/ShoheiImamura/laravel-chapter2.git

# laradock に移動します
cd laravel-chapter2/laradock

# .env ファイルを作成します
cp env-example .env

# docker を立ち上げます
docker-compose up -d nginx mysql

# docker コンテナの中に入ります
docker-compose exec workspace bash
```

### 以下 Docker コンテナ内での作業です

```shell
# composer をインストールします
composer install

# .env ファイルを作成します
cp .env.example .env

# Laravel の api key を作成します
php artisan key:generate
```

上記で環境構築完了です。  
http://localhost にアクセスすると、Laravel の HOME 画面が表示されます。  

Docker コンテナ内の `/var/www/` とローカルフォルダの `/sampleapp` がマウントされていますので、  
sampleapp 以下のファイルを更新すると、Docker 内のファイルも更新されます。

