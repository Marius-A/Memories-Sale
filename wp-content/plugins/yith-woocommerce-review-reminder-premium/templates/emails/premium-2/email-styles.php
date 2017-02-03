<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

// !important; is a gmail hack to prevent styles being stripped if it doesn't like something.

$site_url   = get_option( 'siteurl' );
$assets_url = untrailingslashit( YWRR_ASSETS_URL );

if ( strpos( $assets_url, $site_url ) === false ) {
    $assets_url = $site_url . $assets_url;
}

?>
    body {
    background-color: #65707a;
    -webkit-text-size-adjust: none !important;
    width: 100%;
    margin: 0;
    padding: 0;
    min-width: 100%!important;
    font-family: 'Raleway', sans-serif;
    }

    #content_table{
    width: 100%;
    max-width: 600px;
    }

    #overheader{
    height: 90px;
    }

    #header{
    padding: 10px;
    height: 264px;
    border-top-left-radius: 10px;
    border-top-right-radius: 10px;
    line-height: 40px;
    font-size: 30px;
    text-align: center;
    color: #ffffff;
    background: #6dcbbb;
    }

    #header img{
    display: block;
    margin: 30px auto 40px auto;
    height: 53px;
    }

    #mailbody{
    padding: 50px 40px;
    font-size: 14px;
    color: #656565;
    line-height: 25px;
    background: #ffffff;
    }

    #footer{
    padding: 25px 10px;
    border-bottom-right-radius: 10px;
    border-bottom-left-radius: 10px;
    text-align: center;
    line-height: 20px;
    font-size: 13px;
    background: #444444;
    }

    #footer a{
    text-decoration: none;
    color: #ffffff;
    font-weight: bold;
    }

    #subfooter{
    padding: 10px;
    text-align: center;
    line-height: 20px;
    font-size: 12px;
    color: #bccbd9;
    }

    .items{
    display: block;
    padding: 20px 0;
    color:#429889;
    height: 135px;
    font-size: 16px;
    font-weight: bold;
    text-decoration: none;
    border-bottom: 1px solid #dbdbdb;
    }

    .items img{
    display: block;
    float:left;
    height: 135px;
    margin-right: 20px;
    }

    .items .title{
    display: block;
    margin: 25px 0 0 0;
    }

    .items .stars{
    display: inline-block;
    font-size: 11px;
    color: #6e6e6e;
    line-height: 40px;
    text-transform: uppercase;
    background: url(<?php echo $assets_url ?>/images/rating-stars.png) no-repeat bottom left;
    padding: 0 0 22px 0;
    width: 150px;
    }

<?php
