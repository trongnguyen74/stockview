<?php 
    session_start();

    $host = $_SERVER['HTTP_HOST'];
    if ($host === 'localhost' || strpos($host, '127.0.0.1') !== false) {
        define('URL_ROOT', 'http://localhost/stockview');
    } else {
        define('URL_ROOT', 'https://stockview.vn');
    }
    
    $PATH_URL = str_replace('.php', '', basename($_SERVER['PHP_SELF']));

    switch ($PATH_URL) {
        case 'stat':
            $title = 'Biến động thị trường | StockView';
            $description = 'Xem biến động thị trường, nhóm ngành với thời gian thực';
            $canonical = 'https://stockview.vn/stat.php';
            break;
        case 'iboard':
            $title = 'Bảng giá điện tử | StockView';
            $description = 'Bảng giá điện tử, theo dõi giao dịch thị trường';
            $canonical = 'https://stockview.vn/iboard.php';
            break; 
        case 'chart':
            $title = 'Biểu đồ phân tích kĩ thuật | StockView';
            $description = 'Đồ thị và công cụ phân tích kĩ thuật';
            $canonical = 'https://stockview.vn/chart.php';
            break;    
        case 'quote':
            $stockname = trim($_REQUEST['s']);
            $json = file_get_contents('./data/company/'.$stockname.'.json');
            $detail = json_decode($json);
            $title = $detail->symbol.' : '.$detail->companyName . ' | StockView';
            $description = $detail->companyName . ' - ' . $detail->superSector;
            $canonical = 'https://stockview.vn/quote.php?s='.$stockname;
            break;  
        case 'contact':
            $title = 'Liên hệ | StockView';
            $description = 'Đóng góp ý kiến, phản ánh dịch vụ, hỗ trợ sử dụng';
            $canonical = 'https://stockview.vn/contact.php';
            break;                    
        default:
            $title = 'StockView';
            $description = 'Nền tảng theo dõi thông tin cổ phiếu, thị trường chứng khoán Việt Nam';
            $canonical = 'https://stockview.vn';
            break;
    }

    require_once('redirect_mobile.php');
    require_once('classloader.php');
    require_once('header.php');
?>

<script>
    const URL_ROOT = '<?=URL_ROOT?>';
</script>

<div class="flex-auto h-full mt-12">
    <div class="h-full p-3">
        <?php require_once('views/'.$PATH_URL.'View.php');?>
    </div>
</div>
<?php require_once('footer.php'); ?>