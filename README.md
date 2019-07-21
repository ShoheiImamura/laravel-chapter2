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
- このスライドは reveal.js を使っています
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

### 日本語リファレンス

- [Laravel 5.5 サービスコンテナ
](https://readouble.com/laravel/5.5/ja/container.html)

---

## 2-2-1 サービスコンテナとは

- クラスのインスタンスの生成方法を保持する
- クラスのインスタンスの要求に対してインスタンスを生成して返す
- サービスコンテナの実態は Illuminate\Foundation\Aplication クラス
- サービスコンテナの機能は、継承元の Illuminate\Container\Container クラスに実装される

---

## 2-2-2 バインドと解決

- サービスコンテナに対して、インスタンスの生成方法を登録する処理を「バインド」よ呼ぶ
- 指定されたインスタンスをサービスコンテナが生成して返すことを「解決する」と呼ぶ

![画像]()

### サービスコンテナのインスタンス取得方法

- 3種類の方法を紹介
  1. app 関数から取得する
  2. Application::getInstanceメソッドから取得
  3. Appファサードから取得

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

[AppServiceProviderクラス](https://github.com/ShoheiImamura/laravel-chapter2/blob/master/sampleapp-chapter-2/app/Providers/AppServiceProvider.php#L1-L28)

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

[Github README.md]()

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

[Github README.md]()

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

[Github README.md]()

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

---

## 2章 前半の用語集

| No. | 日本語名         | 英語名        | 説明                                                                                 |
|-----|------------------|---------------|--------------------------------------------------------------------------------------|
| 1   | エントリポイント | entry point   | コンピュータプログラムを構成するコードのうち、最初に実行する事になっている位置のこと |
| 2   | HTTPリクエスト   | HTTP request  | Web ブラウザからからサーバに送信されるデータ送信要求                                 |
| 3   | HTTPレスポンス   | HTTP response | サーバが、HTTPリクエストに対して応答するデータ                                       |
| 4   | オートローダ     | autoloader    | クラスやインターフェイス、トレイトが定義されたPHPファイルを自動で読み込む仕組み      |

---

## Laradock による環境構築

