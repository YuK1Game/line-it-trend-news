<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use Symfony\Component\DomCrawler\Crawler;

class SendLineMessageCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:send_line_message';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $message = sprintf(
            "【Qiitaのトレンド】\n%s\n\n【はてなのトレンド】\n%s",
            $this->createQiitaTrendMessage(),
            $this->createHatenaTrendMessage()
        );

        $httpClient = new CurlHTTPClient(config('linebot.channel_access_token'));
        $bot = new LINEBot($httpClient, ['channelSecret' => config('linebot.channel_secret')]);
        $textMessageBuilder = new TextMessageBuilder($message);
        $bot->broadcast($textMessageBuilder);

        return 0;
    }

    private function createQiitaTrendMessage() {
        $response = \Http::get('https://qiita.com');

        $crawler = new Crawler();
        $crawler->addHtmlContent($response->getBody()->getContents(), 'utf-8');

        $list = collect([]);
        $node = $crawler->filter('div.tr-Item_body')->each(function($node) use($list) {
            $title = $node->filter('a.tr-Item_title')->eq(0)->text() ?? null;
            $url   = $node->filter('a.tr-Item_title')->eq(0)->attr('href') ?? null;
            $list->push([ $title, $url ]);
        });

        return $list->slice(0, 5)->map(function($data) {
            list($title, $url) = $data;
            return sprintf('▼ %s%s%s', $title, "\n", $url);
        })
        ->join("\n\n");
    }

    private function createHatenaTrendMessage() {
        $response = \Http::get('https://b.hatena.ne.jp/hotentry/it');

        $crawler = new Crawler($response->getBody()->getContents());

        $list = collect([]);
        $crawler->filter('.entrylist-contents div.entrylist-contents-main')->each(function($node) use($list) {
            $title = $node->filter('h3 a')->eq(0)->attr('title') ?? null;
            $url   = $node->filter('h3 a')->eq(0)->attr('href') ?? null;
            $list->push([$title, $url]);
        });

        return $list->slice(0, 5)->map(function($data) {
            list($title, $url) = $data;
            return sprintf('▼ %s%s%s', $title, "\n", $url);
        })
        ->join("\n\n");
    }
}
