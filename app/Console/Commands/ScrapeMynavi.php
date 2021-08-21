<?php

namespace App\Console\Commands;

use App\Models\MynaviUrl;
use App\Models\MynaviJob;
use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ScrapeMynavi extends Command
{
    const HOST = "https://tenshoku.mynavi.jp";
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrape:mynavi';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrape Mynavi';

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
      $this->truncateTables();
      $this->saveUrls();
      $this->saveJobs();
    }

    private function truncateTables() {
      DB::table('mynavi_urls')->truncate();
      DB::table('mynavi_jobs')->truncate();
    }

    private function saveUrls() {
      foreach (range(1,2) as $num) {
        $url = "https://tenshoku.mynavi.jp/list/pg" . $num . "/";
        $crawler = \Goutte::request('GET', $url);
        $urls = $crawler->filter('.cassetteRecruit__copy > a')->each(function ($node) {

        $href = $node->attr("href");
          return [
            "url" => substr($href, 0, strpos($href, "/", 1) + 1),
            "created_at" => Carbon::now(),
            "updated_at" => Carbon::now(),
          ];
        });

      DB::table('mynavi_urls')->insert($urls);
      // sleep(5);
      }
    }

    private function saveJobs() {
      foreach(MynaviUrl::all() as $mynaviUrl) {
        $url = $this::HOST . $mynaviUrl->url;
        $crawler = \Goutte::request('GET', $url);
        MynaviJob::create([
          'url' => $url,
          'title' => $this->getTitle($crawler),
          'company_name' => '',
          'features' => '',
        ]);
        break;
        sleep(30);
      }
    }

    private function getTitle($crawler) {
      return $crawler->filter('.occName')->text();
    }
}
