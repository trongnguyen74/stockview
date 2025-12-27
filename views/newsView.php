<?php
    $news = JSON::readJsonFile('./data/news/chung_khoan.json');
    $total = count($news);
?>

<div class="h-[90vh] overflow-y-auto p-6">
  <h1 class="text-xl font-bold mb-4">🔥 Tin mới nhất</h1>

  <!-- Latest News (3 cards on top) -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
    <!-- News Card 1 -->
    <?php for($i=0; $i<4; $i++) :?>
        <a href="news_detail.php?id=<?=($i+1)?>" class="relative group overflow-hidden rounded-2xl bg-white">
            <img src="<?=$news[$i]->media_link?>" 
                class="w-full h-72 object-cover transition-transform duration-300 group-hover:scale-105">
            <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent opacity-70"></div>
            <div class="absolute bottom-4 left-4 right-4 text-white z-10">
                <h2 class="text-xl font-bold leading-tight group-hover:underline"><?=$news[$i]->title?></h2>
                <p class="text-sm mt-2 truncate"><?=$news[$i]->description?></p>
            </div>
        </a>
    <?php endfor;?>
  </div>

  <!-- Older News List -->
  <div class="space-y-6">
    <?php for($i=4; $i<$total-3; $i++) :?>
        <a href="news_detail.php?id=<?=($i+1)?>" class="flex items-start space-x-4 hover:bg-[#f2f2f2] py-3 px-2">
            <img src="<?=$news[$i]->media_link?>" class="w-24 h-24 object-cover rounded-lg">
            <div>
                <h3 class="text-lg font-semibold"><?=$news[$i]->title?></h3>
                <p class="text-sm mt-1 truncate w-[800px] mb-2"><?=$news[$i]->description?></p>
                <span class="text-sm"><?=$news[$i]->pub_date?></span>
            </div>
        </a>
    <?php endfor;?>
  </div>
</div>
