<?php
require 'vendor/autoload.php';
use Amp\ByteStream\ResourceOutputStream;
use Amp\Http\Client\HttpClientBuilder;
use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler\CallableRequestHandler;
use Amp\Http\Server\Response;
use Amp\Http\Server\Router;
use Amp\Http\Server\Server;
use Amp\Http\Status;
use Amp\Log\ConsoleFormatter;
use Amp\Log\StreamHandler;
use Amp\Socket;
use Monolog\Logger;

$hader_keko = ["Access-Control-Allow-Origin" => "*", "Access-Control-Allow-Headers" => "*", 'content-type' => 'application/json', 'Access-Control-Allow-Methods' => "POST, GET, OPTIONS, DELETE, PUT"];
$redis = new Redis();
$redis->pconnect('127.0.0.1', 6379);
Amp\Loop::run(function () use ($redis) {
    $cert = new Socket\Certificate('localhost.pem');
    $context = (new Socket\BindContext)->withTlsContext((new Socket\ServerTlsContext)->withDefaultCertificate($cert));
    $servers = [
        Socket\listen("167.179.81.94:2087", $context),
    ];
    $logHandler = new StreamHandler(new ResourceOutputStream(\STDOUT));
    $logHandler->setFormatter(new ConsoleFormatter);
    $logger = new Logger('server');
    $logger->pushHandler($logHandler);
    $router = new Router;
    $router->addRoute('GET', '/newticket', new CallableRequestHandler(function (Request $request) use ($redis) {
        $id = $redis->incr("Ntickets");
        $redis->rpush("tickets", $id);
        return new Response(Status::OK, $GLOBALS["hader_keko"], json_encode([
            "ok" => true,
            "id" => $id,
        ]));
    }));
    $router->addRoute('GET', '/GetUpdate', new CallableRequestHandler(function (Request $request) use ($redis) {
        $counters = [];
        for ($i=1; $i < 5; $i++) { 
            $counters[$i] = $redis->get("counter:status:$i");
        }
        return new Response(Status::OK, $GLOBALS["hader_keko"], json_encode([
            "ok" => true,
            "counters" => $counters,
            "newserving" => $redis->get("new:ticket:serving"),
            "lastnumber" => $redis->get("Ntickets"),
        ]));
    }));
    $admin = "/admin";
    $router->addRoute('GET', $admin . '/setcounterStatus/{counter}', new CallableRequestHandler(function (Request $request) use ($redis) {
        $args = $request->getAttribute(Router::class);
        if (!isset($args["counter"]) or is_numeric($args["counter"]) == false) {
            return new Response(Status::OK, $GLOBALS["hader_keko"], json_encode([
                "ok" => false,
                "msg" => "pls put counter number",
            ]));
        }
        $n = $redis->get("counter:status:{$args["counter"]}");
        if (!empty($n) and $n != "online"){
            return new Response(Status::OK, $GLOBALS["hader_keko"], json_encode([
                "ok" => false,
                "msg" => "Can not be offline with Ticket"
            ]));
        }
        if ($n) {
            $redis->del("counter:status:{$args["counter"]}");
            $online = false;
        } else {
            $redis->set("counter:status:{$args["counter"]}", "online");
            $online = true;
        }
        return new Response(Status::OK, $GLOBALS["hader_keko"], json_encode([
            "ok" => true,
            "online" => $online,
            "counter" => $args["counter"],
        ]));
    }));
    $router->addRoute('GET', $admin . '/setcounterNext/{counter}', new CallableRequestHandler(function (Request $request) use ($redis) {
        $args = $request->getAttribute(Router::class);
        if (!isset($args["counter"]) or is_numeric($args["counter"]) == false) {
            return new Response(Status::OK, $GLOBALS["hader_keko"], json_encode([
                "ok" => false,
                "msg" => "pls put counter number",
            ]));
        }
        $now = $redis->get("counter:status:{$args["counter"]}");
        $first = $redis->lpop("tickets");
        if (empty($now) or $now == "online") {
            if (empty($first)){
                return new Response(Status::OK, $GLOBALS["hader_keko"], json_encode([
                    "ok" => false,
                    "msg" => "No tickets in the waiting queue",
                ]));
            }
            $redis->set("counter:status:{$args["counter"]}", $first);
            $redis->set("new:ticket:serving", $first);
            return new Response(Status::OK, $GLOBALS["hader_keko"], json_encode([
                "ok" => true,
                "ticket" => $first,
                "counter" => $args["counter"],
            ]));
        } else {
            return new Response(Status::OK, $GLOBALS["hader_keko"], json_encode([
                "ok" => false,
                "msg" => "There is ticket now in this counter",
            ]));
        }
    }));
    $router->addRoute('GET', $admin . '/setcounterComplete/{counter}', new CallableRequestHandler(function (Request $request) use ($redis) {
        $args = $request->getAttribute(Router::class);
        $now = $redis->get("counter:status:{$args["counter"]}");
        $redis->set("counter:status:{$args["counter"]}", "online");
        if (!empty($now) and $now == "online") {
            return new Response(Status::OK, $GLOBALS["hader_keko"], json_encode([
                "ok" => false,
                "msg" => "There is no ticket in this counter",
            ]));
        } else {
            if ($redis->get("new:ticket:serving") == $now){
                $redis->del("new:ticket:serving");
            }
            return new Response(Status::OK, $GLOBALS["hader_keko"], json_encode([
                "ok" => true,
                "online" => true,
            ]));
        }
    }));
    $router->addRoute('GET', $admin . '/getallcounters', new CallableRequestHandler(function (Request $request) use ($redis) {
        $list = [];
        for ($i=1; $i < 5; $i++) { 
            $list[$i] = $redis->get("counter:status:$i");
        }
        return new Response(Status::OK, $GLOBALS["hader_keko"], json_encode([
            "ok" => true,
            "counters" => $list,
        ]));
    }));
    $server = new Server($servers, $router, $logger);
    yield $server->start();
});
