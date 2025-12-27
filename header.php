<!DOCTYPE html>
<html lang="vi">
<head>
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-5HC85M2J');</script>
    <!-- End Google Tag Manager -->

    <meta name="google-site-verification" content="ynUxVSOlfDfL3Cow5r898aIJ4hv_cNb2l_e77tv2GV4" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="index,follow,all">
    <meta name="google-adsense-account" content="ca-pub-7984800412058336">
    
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-7984800412058336"
     crossorigin="anonymous">
    </script>
    
    <!-- SEO Meta Description -->
    <meta name="description" content="<?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?>">
    <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></title>
    
    <!-- Favicon and Apple Touch Icon -->
    <link rel="icon" type="image/png" href="public/favicon.png">
    <link rel="apple-touch-icon" href="public/favicon.png">

    <!-- External Stylesheets -->
    <link rel="stylesheet" href="styles.css">
    <link rel="canonical" href="<?= htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8') ?>"/>

    <!-- Scripts -->
    <script src="js/fontawesome.js"></script>
    <script src="js/tailwindcss.js"></script>
    <script src="js/jquery.min.js"></script>
    <script src="js/google-account-client.js"></script>
    <script src="js/google-account-api.js"></script>
    <script src="js/google-account-platform.js" async></script>
    
    <?php 
        date_default_timezone_set("Asia/Ho_Chi_Minh");
    ?>
</head>
<body class="flex flex-col">
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-5HC85M2J"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->

    <header class="w-full bg-[#252a31] flex-initial fixed top-0 z-100000 py-2 border-b border-[#595959]">
        <!-- Top Bar -->
        <div class="flex justify-between h-10 px-4">
            <div class="flex items-center h-full space-x-4">
                <a class="flex items-center space-x-1 h-full" href="index.php">
                    <img class="h-[80%]" src="public/logo.png" alt="StockView Logo">
                </a>
                <nav class="flex">
                    <?php 
                        $menus = [
                            'index'  => 'Trang chủ',
                            'stat'   => 'Biến động',
                            'iboard' => 'Bảng giá',
                            'basic_chart'  => 'Biểu đồ',
                        ];
                        foreach($menus as $key => $label) {
                            // Set target attribute for external link
                            $selected = (isset($PATH_URL) && $PATH_URL === $key) ? ' selected' : '';
                            $href = $key . '.php';
                            echo '<a href="' . $href . '"' . ' class="menu' . $selected . '">' . $label . '</a>';
                        }
                    ?>
                </nav>
            </div>
            <div class="flex items-center">
                <nav class="flex items-center space-x-4">
                    <a href="contact.php"><i class="fa-solid fa-mobile-screen-button"></i>&nbsp;Liên hệ</a>
                    <?php if(isset($_SESSION["client_full_name"]) && !empty($_SESSION["client_full_name"])) : ?>
                        <a class="bg-[#248f24] rounded px-2" href="login.php"><?= htmlspecialchars($_SESSION["client_full_name"], ENT_QUOTES, 'UTF-8') ?></a>
                    <?php else: ?>
                        <a href="login.php"><i class="fa-solid fa-arrow-right-to-bracket"></i>&nbsp;Đăng nhập</a>
                    <?php endif; ?>
                </nav>
                <!-- Navigation Menu and Search -->
                <div class="flex items-center h-8 px-2 justify-between">
                    <div class="relative h-full">
                        <form class="h-full" method="get" action="quote.php">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input
                                type="text"
                                name="s"
                                class="h-full pl-10 focus:outline-none focus:ring-2 focus:ring-blue-400"
                                placeholder="Nhập mã cổ phiếu..."
                            />
                        </form>
                    </div>    
                </div>
            </div>
        </div>
    </header>
