<?php 
    $id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : '1';
    $stockname = trim($_REQUEST['s']) ? trim($_REQUEST['s']) : 'vnm';
    $res = JSON::readJsonFile('https://iboard-api.ssi.com.vn/statistics/company/news?symbol='.strtoupper($stockname).'&pageSize=8&page=1&language=vn', true);
    $news = $res->data ?? [];
    $detail = $news[$id-1];
    unset($news[$id-1]);
?>
<div class="h-[90vh] overflow-y-auto px-4">
    <div class="max-w-7xl mx-auto px-4 py-10 flex flex-col lg:flex-row gap-10">
        <!-- Main News Content -->
        <article class="lg:w-2/3">
            <h1 class="text-3xl font-bold mb-4 leading-snug"><?= $detail->title ?></h1>
            <div class="flex items-center text-sm mb-6 space-x-4">
                <span>🗓️ <?= $detail->publicDate ?></span>
                <span>🏷️ <?= $detail->symbol ?></span>
            </div>
            <img src="<?= $detail->imageUrl ?>" alt="thumbnail" class="w-full mb-6 rounded-xl">
            <div class="prose prose-invert max-w-none prose-img:rounded-lg prose-a:text-blue-400 text-base leading-relaxed space-y-4 text-justify">
                <?php echo $detail->fullContent; ?>
            </div>
        </article>

        <!-- Sidebar -->
        <aside class="lg:w-1/3 box-content p-2 h-fit border-1 border-[#595959]">
            <h2 class="text-xl font-semibold mb-4">📰 Tin khác</h2>

            <?php foreach ($news as $i=>$new): ?>
                <a href="company_news_detail.php?s=<?=$stockname?>&id=<?=($i+1)?>" class="flex items-start gap-4 mb-5 p-2">
                    <img src="<?= $new->imageUrl ?>" alt="thumb" class="w-20 h-14 object-cover">
                    <div>
                        <h3 class="text-sm font-medium leading-snug line-clamp-2"><?= $new->title ?></h3>
                        <p class="text-xs mt-1"><?= $new->publicDate ?></p>
                    </div>
                </a>
            <?php endforeach; ?>
        </aside>
    </div>
</div>