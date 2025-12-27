<?php 
    $inputs = [];
    $stockname = '';

    if(isset($_REQUEST['s'])){
        $stockname = trim($_REQUEST['s']);
        $a_stock = JSON::readJsonFile('./data/in_stockname.json');

        if (!in_array($stockname, $a_stock)) {
            exitWithMessage("Có vẻ mã chứng khoán bạn đang tìm không có. Vui lòng nhập lại. Cảm ơn!");
        }
        $_SESSION['inputs']['s'] = $stockname;
        $inputs = $_SESSION['inputs'];
        $query = http_build_query($inputs);
        $src   = "draw_chart.php?$query";
    }
    else{
        $stockname = 'ssi';
        $_SESSION['inputs']['s'] = $stockname;
        $inputs = $_SESSION['inputs'];
        $query = http_build_query($inputs);
        $src   = "draw_chart.php?$query";
    }


    if (isset($_POST['submit'])) {
        $inputs = [];

        // Collect all form inputs from POST
        foreach ($_POST as $name => $value) {
            // Skip the submit button itself
            if ($name === 'submit') {
                continue;
            }
            $inputs[$name] = $value;
        }

        // Build a query string so we can still pass them via GET to draw_chart.php
        $query = http_build_query($inputs);
        $src   = "draw_chart.php?$query";

        // Store for persistence or later use
        $_SESSION['inputs'] = $inputs;
    }

    // Utility function for clean error handling
    function exitWithMessage(string $message): void {
        echo '<div class="w-full h-full flex items-center justify-center">
                <span>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</span>
            </div>';
        exit;
    }
?>

<div class="relative flex flex-col m-0 h-full space-y-2 p-2 text-white">
  <!-- Header with Search & Controls -->
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-6">
            <div class="relative">
                <!-- Settings Button -->
                <button
                    id="settingsToggle"
                    class="z-10 px-2 py-1 bg-gray-700 text-white rounded hover:bg-gray-600"
                >
                    ⚙️ Cài đặt
                </button>

                <!-- Small Settings Panel -->
                <div
                    id="settingsPanel"
                    class="absolute top-10 left-0 z-10 bg-gray-800 text-white rounded shadow-lg p-4 space-y-3 hidden border-1 border-[#FFF]"
                    style="width: 240px;"
                >
                    <form action="basic_chart.php" method="post" class="space-y-2 text-sm">
                        <input type="hidden" name="s" value="<?= htmlspecialchars($stockname, ENT_QUOTES) ?>" />

                        <!-- Volume -->
                        <label class="flex items-center mb-4">
                            <input
                            type="checkbox"
                            name="volume"
                            value="1"
                            class="form-checkbox text-indigo-400"
                            <?= isset($inputs['volume']) ? 'checked' : '' ?>
                            />
                            <span class="ml-2">Volume</span>
                        </label>

                        <!-- SMA -->
                        <div class="mb-4">
                            <div class="font-medium mb-1">SMA</div>
                            <div class="grid grid-cols-2 gap-2">
                            <?php for ($i = 1; $i <= 4; $i++): ?>
                                <label class="flex items-center">
                                <input
                                    type="checkbox"
                                    name="sma<?= $i ?>"
                                    value="1"
                                    class="form-checkbox text-yellow-400"
                                    <?= isset($inputs["sma{$i}"]) ? 'checked' : '' ?>
                                />
                                <input
                                    type="number"
                                    name="smap<?= $i ?>"
                                    value="<?= htmlspecialchars($inputs["smap{$i}"] ?? [10,20,50,100][$i-1], ENT_QUOTES) ?>"
                                    min="1"
                                    class="ml-1 w-12 bg-gray-700 rounded px-1 py-0.5 text-xs"
                                />
                                </label>
                            <?php endfor; ?>
                            </div>
                        </div>

                        <!-- EMA -->
                        <div class="mb-4">
                            <div class="font-medium mb-1">EMA</div>
                            <div class="grid grid-cols-2 gap-2">
                            <?php for ($i = 1; $i <= 4; $i++): ?>
                                <label class="flex items-center">
                                <input
                                    type="checkbox"
                                    name="ema<?= $i ?>"
                                    value="1"
                                    class="form-checkbox text-red-400"
                                    <?= isset($inputs["ema{$i}"]) ? 'checked' : '' ?>
                                />
                                <input
                                    type="number"
                                    name="emap<?= $i ?>"
                                    value="<?= htmlspecialchars($inputs["emap{$i}"] ?? [10,20,50,60][$i-1], ENT_QUOTES) ?>"
                                    min="1"
                                    class="ml-1 w-12 bg-gray-700 rounded px-1 py-0.5 text-xs"
                                />
                                </label>
                            <?php endfor; ?>
                            </div>
                        </div>

                        <!-- Other Indicators -->
                        <?php $others = [
                            'rsi'     => 14,
                            'mfi'     => 14,
                            'cci'     => 14,
                            'william' => 14,
                        ]; ?>
                        <div class="space-y-2 mt-10">
                            <?php foreach ($others as $name => $default): ?>
                            <label class="grid grid-cols-2 gap-2">
                                <div class="flex items-center space-x-2">
                                <input
                                    type="checkbox"
                                    name="<?= $name ?>"
                                    value="1"
                                    class="form-checkbox text-indigo-400"
                                    <?= isset($inputs[$name]) ? 'checked' : '' ?>
                                />
                                <span class="capitalize"><?= strtoupper($name) ?></span>
                                </div>
                                <input
                                type="number"
                                name="<?= $name ?>p"
                                value="<?= htmlspecialchars($inputs["{$name}p"] ?? $default, ENT_QUOTES) ?>"
                                min="1"
                                class="ml-1 w-12 bg-gray-700 rounded px-1 py-0.5 text-xs"
                                />
                            </label>
                            <?php endforeach; ?>

                            <!-- MACD only checkbox -->
                            <label class="flex items-center">
                            <input
                                type="checkbox"
                                name="macd"
                                value="1"
                                class="form-checkbox text-gray-400"
                                <?= isset($inputs['macd']) ? 'checked' : '' ?>
                            />
                            <span class="ml-2">MACD</span>
                            </label>
                        </div>

                        <!-- Apply Button -->
                        <button
                            type="submit"
                            name="submit"
                            value="1"
                            class="w-full mt-2 px-3 py-1 bg-blue-600 hover:bg-blue-700 rounded"
                        >
                            Apply
                        </button>
                    </form>
                </div>
            </div>

            <div class="flex items-center space-x-6">
                <form method="get" class="flex space-x-2">
                <input
                    type="text"
                    name="s"
                    placeholder="Nhập mã"
                    class="form-input text-white placeholder-gray-400 border-gray-700 focus:border-blue-500 focus:ring-blue-500"
                />
                </form>
            </div>
        </div>
        <a
            class="stockname hover:underline"
            href="chart.php"
        >
            Xem biểu đồ nâng cao
        </a>
    </div>

    <!-- Chart Image -->
    <div class="relative flex-auto">
        <img
        src="<?= htmlspecialchars($src, ENT_QUOTES) ?>"
        class="w-full h-auto rounded bg-black"
        alt="Stock Chart"
        />
    </div>
</div>

<script>
  const toggle = document.getElementById('settingsToggle');
  const panel  = document.getElementById('settingsPanel');
  toggle.addEventListener('click', () => panel.classList.toggle('hidden'));
</script>
