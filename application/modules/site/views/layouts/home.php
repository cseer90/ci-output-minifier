<?php
/**
 * Created by PhpStorm.
 * User: Sayed
 * Date: 09-02-2016
 * Time: 12:39
 */
$data = empty($data) ?[] :$data;
?>
<html>
    <head>
        <title>Site Title</title>
        <link rel="stylesheet" href="<?php echo Resource::css('assets/bootstrap/css/bootstrap.min.css')?>" />
        <link rel="stylesheet" href="<?php echo Resource::css('assets/bootstrap/css/bootstrap-theme.min.css')?>" />
        <link rel="stylesheet" href="<?php echo Resource::css('assets/site/css/index.css')?>" />
        <link rel="stylesheet" href="<?php echo Resource::css('assets/site/css/homepage.css')?>" />
        <link rel="stylesheet" href="<?php echo Resource::css('assets/site/css/guest.css')?>" />
        <script>
            var jsHome = '<?php echo base_url()?>';
        </script>
    </head>
    <body>
        <div class="page">
            <div class="container-fluid home_top"><?php $this->load->view('top')?></div>
            <div class="clearfix"></div>
            <div class="body_wrapper container-fluid clearfix">
                <?php $this->load->view('homepage',$data)?>
            </div>
            <div class="footer"><?php $this->load->view('footer')?></div>
        </div>
        <script src='<?php echo Resource::js('assets/site/js/index.js')?>'></script>
        <script src='<?php echo Resource::js('assets/site/js/jquery1.10.2.js')?>'></script>
    </body>
</html>
