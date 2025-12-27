<?php
    if(isset($_REQUEST['login'])){
        $email = trim($_REQUEST['email']);
        $fullName = trim($_REQUEST['fullName']);
        $lastLogged = trim($_REQUEST['lastLogged']);
        $client = JSON::readJsonFile('./data/client.json');

        $keys = array_column($client, 'email');
        $index = array_search($email, $keys);

        if ($index === false) {
            $obj = new stdClass();
            $obj->email = $email;
            $obj->full_name = $fullName;
            $obj->last_logged = $lastLogged;
            $obj->watchlist = '';
            array_push($client, $obj);
            JSON::writeJsonFile('./data/client.json', $client);
            $_SESSION["client_watchlist"] = '';
        }
        else{
            $client[$index]->last_logged = $lastLogged;
            JSON::writeJsonFile('./data/client.json', $client);
            $_SESSION["client_watchlist"] = $client[$index]->watchlist;
        }
        $_SESSION["client_full_name"] = $fullName;
        $_SESSION["client_email"] = $email;
        exit;
    }
    else if(isset($_REQUEST['logout'])){
        unset($_SESSION['client_full_name']);
        unset($_SESSION['client_watchlist']);
        unset($_SESSION['client_email']);
        header('Location: login.php');
    }
?>

<div class="p-4 space-y-4">
    <h1 class="uppercase font-bold">Đăng nhập làm thành viên của stockview để nhận thêm nhiều ưu đãi</h1>
    <?php if($_SESSION["client_full_name"] == '') :?>
        <div id="g_id_onload"
            data-client_id="140018260040-ad315kj98n9m36lah83e9a9riel8q1mr"
            data-callback="handleCredentialResponse">
        </div>
        <div class="g_id_signin" data-type="standard"></div>
    <?php else: ?>
        <div>Bạn đang đăng nhập với tên người dùng: <?=$_SESSION["client_full_name"]?></div>
        <a class="bg-[#FF0000] text-[#FFF] p-2 rounded" href="login.php?logout=1">Đăng xuất</a>
    <?php endif; ?>
</div>

<script>
    function handleCredentialResponse(response) {
        const responsePayload = decodeJwtResponse(response.credential);

        let fullName = responsePayload.name;
        let email = responsePayload.email;

        var currentdate = new Date(); 
        var lastLogged = currentdate.getDate() + "/"
                + (currentdate.getMonth()+1)  + "/" 
                + currentdate.getFullYear() + " @ "  
                + currentdate.getHours() + ":"  
                + currentdate.getMinutes() + ":" 
                + currentdate.getSeconds();

        $.ajax({
            type: "POST",
            url: document.location.href,
            data: {'login': 1, 'email': email, 'fullName': fullName, 'lastLogged': lastLogged},
            cache: false,
            success: function(res){
                //console.log(res);
                window.location.reload();
            }
        })
    }

    function decodeJwtResponse(token) {
        let base64Url = token.split('.')[1];
        let base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
        let jsonPayload = decodeURIComponent(atob(base64).split('').map(function(c) {
            return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
        }).join(''));

        return JSON.parse(jsonPayload);
    }
  </script>