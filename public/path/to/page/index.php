<?php

/**
 * PHP Debugging & Benchmarking Tools.
 *
 * @author [deepeloper](https://github.com/deepeloper)
 * @license [MIT](https://opensource.org/licenses/mit-license.php)
 */

use deepeloper\Debeetle\d;
use deepeloper\Debeetle\Loader;

error_reporting(E_ALL);
ini_set("display_errors", "1");

/**
 * Place this struct definition to the every script entry point you will debug.
 */
$scriptInitState = [
    'serverTime' => isset($_SERVER['REQUEST_TIME']) ? $_SERVER['REQUEST_TIME'] : time(),
    'time' => isset($_SERVER['REQUEST_TIME_FLOAT']) ? $_SERVER['REQUEST_TIME_FLOAT'] : microtime(true),
    'memoryUsage' => memory_get_usage(),
    'peakMemoryUsage' => function_exists('memory_get_peak_usage') ? memory_get_peak_usage() : null,
    'entryPoint' => [
        'file' => __FILE__,
        'line' => __LINE__,
    ],
];

$debeetleInitState = [
    'time' => microtime(true),
    'memoryUsage' => memory_get_usage(),
    'peakMemoryUsage' => function_exists('memory_get_peak_usage') ? memory_get_peak_usage() : null,
    'includedFiles'   => sizeof(get_included_files()),
    'entryPoint' => [
        'file' => __FILE__,
        'line' => __LINE__,
    ],
];

$autoloadPath = realpath("./../../../../vendor/autoload.php");
$configPath = realpath("./../../../config.xml.php");
require_once $autoloadPath;

try {
    Loader::startup([
        'config' => realpath($configPath),
        'developerMode' => true, // To see startup errors.
        'scriptInitState' => $scriptInitState,
        'initState' => $debeetleInitState,
    ]);
    // Add example locales.
    /**
     * @var deepeloper\Debeetle\DebeetleInterface $debeetle
     */
    $debeetle = d::getInstance();
    if ($debeetle) {
        $settings = $debeetle->getSettings();
        foreach (array_unique([$settings['defaults']['language'], "en"]) as $language) {
            $path = sprintf("%s/locales/%s.php",__DIR__, $language);
            if (file_exists($path)) {
                $debeetle->getView()->addLocales(require $path);
                break;
            }
        }
    }
    unset($debeetle, $settings, $path, $language);
} catch (Exception $debeetleException) { // @todo Replace with Throwable for PHP >= 7.
}
unset($configPath, $vendorPath, $scriptInitState, $debeetleInitState);

/*echo '<pre>';
echo 'time diff: '; var_dump(microtime(true) - $scriptInitState['time']);
echo 'included files diff: '; var_dump(sizeof(get_included_files()) - $debeetleInitState['includedFiles']);
echo 'mem usage diff: '; var_dump(memory_get_usage() - $scriptInitState['memoryUsage']);
echo 'peak mem usage diff: '; var_dump(memory_get_peak_usage() - $scriptInitState['peakMemoryUsage']);
echo '</pre>';*/

// } Debeetle initialization

function highlight($code)
{
    d::w(highlight_string("<?php $code", true), ['htmlEntities' => false]);
}

d::t("examples|common");

d::bs("examples");
d::bs("examples");

d::cp("my checkpoint");

d::t("examples|common");

highlight('d::du(null);');
d::du(null);

highlight('d::du(false);');
d::du(false);

highlight('d::du(1);');
d::du(1);

highlight('d::du(1.34);');
d::du(1.34);

highlight('d::w("&raquo;&raquo;\n", [\'htmlEntities\' => false]);');
d::w("&raquo;&raquo;\n", ['htmlEntities' => false]);

highlight('d::du([1, 2, 3]);');
d::du([1, 2, 3]);

highlight('d::du("\'", \'quote\');');
d::du("'", 'quote');

highlight('d::du(\'"\', \'double quote\');');
d::du('"', 'double quote');

highlight('d::du("Long Long Long...", "", [\'maxStringLength\' => 30]);');
d::du(
    "Long Long Long Long Long Long Long Long Long Long Long Long Long Long Long Long Long Long Long Long Long" .
    "Long Long Long Long Long Long Long Long Long Long Long Long Long Long Long Long Long Long Long Long Long Long " .
    "Long Long Long Long Long Long Long Long Long Long Long Long Long Long Long Long Long Long Long Long Long Long " .
    "Long Long Long Long Long Long Long Long Long Long Long Long Long Long Long Long Long Long Long Long Long Long",
    "",
    ['maxStringLength' => 30]
);

$code = <<<EOT


\$fh = fopen(\$_SERVER['SCRIPT_FILENAME'], "r");
d::du(\$fh);
EOT;
highlight($code);
eval("use deepeloper\Debeetle\d; $code");

$code = <<<EOT


#[AllowDynamicProperties]
class Foo
{
    public \$x = 1;
    public \$nestingEntities = [1, 2, ['key' => 1]];
    public \$publicNull;
    protected \$protectedNull;
    private \$privateNull;

    public function __construct()
    {
        \$this->traceCaller(\$this);
    }

    public function traceCaller(\$object)
    {
        d::trace();
    }
}
\$foo = new Foo;
\$foo->nestingEntities[] = \$foo;
EOT;

highlight($code);
eval("use deepeloper\Debeetle\d; $code");

highlight('d::du($foo, \'Foo!\');');
/**
 * @var Foo $foo
 */
d::du($foo, 'Foo!');

d::t("examples|backslashedTabName");

highlight('d::w("Single backslash \\\\");');
d::w("Single backslash \\");


$code = <<<EOT


d::t("examples|nestedTabs");
d::t("examples|nestedTabs|level3");
d::t("examples|nestedTabs|level3|level4");
d::t("examples|nestedTabs|level3|level4|level5");
EOT;
eval("use deepeloper\Debeetle\d; $code");
highlight($code);

session_start();

d::t("environment");
d::t("environment|get");
d::dump($_GET);
d::t("environment|post");
d::dump($_POST);
d::t("environment|cooklie");
d::dump($_COOKIE);
d::t("environment|request");
d::dump($_REQUEST);
d::t("environment|session");
d::dump($_SESSION);
d::t("environment|server");
// d::dump($_SERVER);
d::w("Here has to be <code>d::dump(\$_SERVER);</code>", ['htmlEntities' => false]);

trigger_error("User notice");
trigger_error("User warning", E_USER_WARNING);
trigger_error("User deprecated", E_USER_DEPRECATED);
trigger_error("User error", E_USER_ERROR);
if (version_compare(phpversion(), "8", "<")) {
    $_SERVER['undefined']; // E_NOTICE
}
$a = 10; foreach ($a as $b) {}// E_WARNING

d::cp("my checkpoint");

d::be("examples");
d::be("examples");
d::be("unknown");
d::t("benchmarks", null, ["after:reports"]);
d::w(
"d::getBenchmarks() returns array containing bechmarks labels as keys and array<br/>" .
     "['count' => (int)count of calls, 'total' => (double)total time]."
);
d::du(d::getBenchmarks(), "Benchmarks");
d::w(
    "d::getCheckpoints() returns array containing checkpoints labels as keys and array<br/>" .
    "['count' => (int)count of calls, 'data' (if storeData is true) => [<br/>" .
    "&nbsp;&nbsp;(double)time to the next call,<br/>" .
    "&nbsp;&nbsp;(int)memory usage,<br/>" .
    "&nbsp;&nbsp;(int)peak memory usage<br/>" .
    "]]."
);
d::du(d::getCheckpoints(), "Checkpoints");

//if (false === strpos($_SERVER['REQUEST_URI'], "?1")) {
//  header("Location: http://deepeloper.home/subfolder/?1");
//  die;
//}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <!-- basic -->
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <!-- mobile metas -->
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="viewport" content="initial-scale=1, maximum-scale=1">
  <!-- site metas -->
  <title>Deebetle (PHP Debugging Tool) Live Demo</title>
  <meta name="keywords" content="">
  <meta name="description" content="">
  <meta name="author" content="">
  <!-- bootstrap css -->
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <!-- style css -->
  <link rel="stylesheet" href="css/style.css">
  <!-- Responsive-->
  <link rel="stylesheet" href="css/responsive.css">
  <!-- favicon -->
  <link rel="icon" href="images/favicon.png" type="image/gif" />
  <!-- Scrollbar Custom CSS -->
  <link rel="stylesheet" href="css/jquery.mCustomScrollbar.min.css">
  <!-- Tweaks for older IEs-->
  <link rel="stylesheet" href="https://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/2.1.5/jquery.fancybox.min.css" media="screen">
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script><![endif]-->
  <link rel="icon" type="image/png" href="images/favicon.png" />
</head>
<!-- body -->
<body class="main-layout">
<!-- loader  -->
<div class="loader_bg">
  <div class="loader"><img src="images/loading.gif" alt="#"/></div>
</div>
<!-- end loader -->
<!-- header -->
<header class="full_bg">
  <!-- header inner -->
  <div class="header">
    <div class="container-fluid">
      <div class="row">
        <div class="col-xl-3 col-lg-3 col-md-3 col-sm-3 col logo_section">
          <div class="full">
            <div class="center-desk">
              <div class="logo">
                <a href="index.html"><img src="images/logo.png" alt="#" /></a>
              </div>
            </div>
          </div>
        </div>
        <div class="col-xl-9 col-lg-9 col-md-9 col-sm-9">
          <nav class="navigation navbar navbar-expand-md navbar-dark ">
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExample04" aria-controls="navbarsExample04" aria-expanded="false" aria-label="Toggle navigation">
              <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarsExample04">
              <ul class="navbar-nav mr-auto">
                <li class="nav-item active">
                  <a class="nav-link" href="index.html">Home</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="about.html">About</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="classes.html">classes</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="yoga.html">yoga</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="pricing.html">pricing</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="contact.html">Contact Us</a>
                </li>
              </ul>
            </div>
          </nav>
        </div>
      </div>
    </div>
  </div>
  <!-- end header inner -->
  <!-- end header -->
  <!-- banner -->
  <section class="banner_main">
    <div id="myCarousel" class="carousel slide banner" data-ride="carousel">
      <ol class="carousel-indicators">
        <li data-target="#myCarousel" data-slide-to="0" class="active"></li>
        <li data-target="#myCarousel" data-slide-to="1"></li>
        <li data-target="#myCarousel" data-slide-to="2"></li>
      </ol>
      <div class="carousel-inner">
        <div class="carousel-item active">
          <div class="container">
            <div class="carousel-caption  banner_po">
              <div class="row">
                <div class="col-md-5">
                  <div class="yo_img">
                    <figure><img src="images/yo_img.png" alt="#"/></figure>
                  </div>
                </div>
                <div class="col-md-7">
                  <div class="yoga_box">
                    <span>Now started</span>
                    <h1> <strong>Y</strong> O <strong>B</strong> A</h1>
                    <a class="read_more yoga_btn" href="#" role="button">Contact us</a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="carousel-item">
          <div class="container">
            <div class="carousel-caption banner_po">
              <div class="row">
                <div class="col-md-5">
                  <div class="yo_img">
                    <figure><img src="images/yo_img.png" alt="#"/></figure>
                  </div>
                </div>
                <div class="col-md-7">
                  <div class="yoga_box">
                    <span>Now started</span>
                    <h1> <strong>Y</strong> O <strong>B</strong> A</h1>
                    <a class="read_more yoga_btn" href="#role="button">Contact us</a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="carousel-item">
          <div class="container">
            <div class="carousel-caption banner_po">
              <div class="row">
                <div class="col-md-5">
                  <div class="yo_img">
                    <figure><img src="images/yo_img.png" alt="#"/></figure>
                  </div>
                </div>
                <div class="col-md-7">
                  <div class="yoga_box">
                    <span>Now started</span>
                    <h1> <strong>Y</strong> O <strong>B</strong> A</h1>
                    <a class="read_more yoga_btn " href="#" role="button">Contact us</a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <a class="carousel-control-prev" href="#myCarousel" role="button" data-slide="prev">
        <i class="fa fa-arrow-left" aria-hidden="true"></i>
        <span class="sr-only">Previous</span>
      </a>
      <a class="carousel-control-next" href="#myCarousel" role="button" data-slide="next">
        <i class="fa fa-arrow-right" aria-hidden="true"></i>
        <span class="sr-only">Next</span>
      </a>
    </div>
  </section>
</header>
<!-- end banner -->
<!-- our classes -->
<div class="classes">
  <div class="container">
    <div class="row">
      <div class="col-sm-12">
        <div class="titlepage">
          <h2>Our Classes</h2>
          <span>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor
                     </span>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-4 col-sm-6 d_none">
      </div>
      <div class="col-md-4 col-sm-6 margin_bott">
        <div class="our_yoga">
          <figure><img src="images/our_yoga1.png" alt="#"/></figure>
          <h3>HATHA YOBA</h3>
          <span>Lorem ipsum dolor sit amet</span>
        </div>
      </div>
      <div class="col-md-4 col-sm-6 d_none">
      </div>
      <div class="col-md-4 col-sm-6">
        <div class="our_yoga">
          <figure><img src="images/our_yoga2.png" alt="#"/></figure>
          <h3>HATHA YOBA</h3>
          <span>Lorem ipsum dolor sit amet</span>
        </div>
      </div>
      <div class="col-md-4 col-sm-6 d_none">
      </div>
      <div class="col-md-4 col-sm-6">
        <div class="our_yoga">
          <figure><img src="images/our_yoga3.png" alt="#"/></figure>
          <h3>HATHA YOBA</h3>
          <span>Lorem ipsum dolor sit amet</span>
        </div>
      </div>
      <div class="col-md-4 offset-md-4 col-sm-6  margin_top">
        <div class="our_yoga">
          <figure><img src="images/our_yoga4.png" alt="#"/></figure>
          <h3>HATHA YOBA</h3>
          <span>Lorem ipsum dolor sit amet</span>
          <a class="read_more yoga_btn" href="Javascript:void(0)"> Read More</a>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- end our classes -->
<!-- middle -->
<div class="middle">
  <div class="container-fluid">
    <div class="row d_flex">
      <div class="col-md-6">
        <div class="titlepage">
          <h2 >The Inner Middle.</h2>
          <p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptu</p>
          <a class="read_more ye_b5n " href="Javascript:void(0)"> Read More</a>
        </div>
      </div>
      <div class="col-md-5 offset-md-1 padding_right0">
        <div class="yoga_img">
          <figure><img src="images/yoga_mo.png" alt="#"/></figure>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- end middle -->
<!-- about -->
<div class="about">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-6 offset-md-3">
        <div class="titlepage">
          <h2>About Us</h2>
          <span>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptu</span>
        </div>
      </div>
      <div class="col-md-12">
        <div class="about_img">
          <figure><img src="images/about.png" alt="#"/></figure>
          <a class="read_more yoga_btn" href="Javascript:void(0)"> Read More</a>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- end about -->
<!-- balance -->
<div class="balance">
  <div class="container-fluid">
    <div class="row d_flex">
      <div class="col-md-5 padding_left0">
        <div class="yoga_img">
          <figure><img src="images/yoga_mo1.png" alt="#"/></figure>
        </div>
      </div>
      <div class="col-md-6 offset-md-1">
        <div class="titlepage">
          <h2 class="padd_top30">Mind in Balance</h2>
          <p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptu</p>
          <a class="read_more ye_b5n " href="Javascript:void(0)"> Read More</a>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- end balance -->
<!-- end middle -->
<!-- pricing -->
<div class="pricing_main">
  <div class="container">
    <div class="row">
      <div class="col-md-12">
        <div class="titlepage">
          <h2>Pricing</h2>
          <span>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy </span>
        </div>
      </div>
      <div class="col-md-4">
        <div class="pricing">
          <h4>STARTER PLAN</h4>
          <h3><span>$</span>60</h3>
          <p>Unlimited access to content Fully Secure online backup One Year round the clock support FREE complimentary lanyard</p>
          <a class="read_more dark_bg" href="Javascript:void(0)"> Starte Now</a>
        </div>
      </div>
      <div class="col-md-4 ho_bor">
        <div class="pricing ">
          <h4>STARTER PLAN</h4>
          <h3><span>$</span>60</h3>
          <p>Unlimited access to content Fully Secure online backup One Year round the clock support FREE complimentary lanyard</p>
          <a class="read_more dark_bg" href="Javascript:void(0)"> Starte Now</a>
        </div>
      </div>
      <div class="col-md-4">
        <div class="pricing">
          <h4>STARTER PLAN</h4>
          <h3><span>$</span>60</h3>
          <p>Unlimited access to content Fully Secure online backup One Year round the clock support FREE complimentary lanyard</p>
          <a class="read_more dark_bg" href="Javascript:void(0)"> Starte Now</a>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- end pricing -->
<!-- pepole -->
<div class="pepole">
  <div class="container">
    <div class="row">
      <div class="col-md-12">
        <div class="titlepage">
          <h2>What Says Pepole</h2>
          <span>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy </span>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-8 offset-md-2">
        <div class="testimo_ban_bg">
          <div id="testimo" class="carousel slide testimo_ban" data-ride="carousel">
            <ol class="carousel-indicators">
              <li data-target="#testimo" data-slide-to="0" class="active"></li>
              <li data-target="#testimo" data-slide-to="1"></li>
              <li data-target="#testimo" data-slide-to="2"></li>
            </ol>
            <div class="carousel-inner">
              <div class="carousel-item active">
                <div class="container parile0">
                  <div class="carousel-caption relative2">
                    <div class="row d_flex">
                      <div class="col-md-12">
                        <i><img class="qusright" src="images/t.png" alt="#"/><img src="images/tes.png" alt="#"/><img class="qusleft" src="images/t.png" alt="#"/></i>
                        <div class="aliq">
                          <span>Aliqua</span>
                          <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniamLorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="carousel-item">
                <div class="container parile0">
                  <div class="carousel-caption relative2">
                    <div class="row d_flex">
                      <div class="col-md-12">
                        <i><img class="qusright" src="images/t.png" alt="#"/><img src="images/tes.png" alt="#"/><img class="qusleft" src="images/t.png" alt="#"/></i>
                        <div class="aliq">
                          <span>Aliqua</span>
                          <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniamLorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="carousel-item">
                <div class="container parile0">
                  <div class="carousel-caption relative2">
                    <div class="row d_flex">
                      <div class="col-md-12">
                        <i><img class="qusright" src="images/t.png" alt="#"/><img src="images/tes.png" alt="#"/><img class="qusleft" src="images/t.png" alt="#"/></i>
                        <div class="aliq">
                          <span>Aliqua</span>
                          <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniamLorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <a class="carousel-control-prev" href="#testimo" role="button" data-slide="prev">
                <i class="fa fa-arrow-left" aria-hidden="true"></i>
                <span class="sr-only">Previous</span>
              </a>
              <a class="carousel-control-next" href="#testimo" role="button" data-slide="next">
                <i class="fa fa-arrow-right" aria-hidden="true"></i>
                <span class="sr-only">Next</span>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- end pepole -->
<!--  contact -->
<div class="contact">
  <div class="container">
    <div class="row">
      <div class="col-md-12">
        <div class="titlepage">
          <h2>Contact Us</h2>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-6 padding_right0">
        <form id="request" class="main_form">
          <div class="row">
            <div class="col-md-12 ">
              <input class="contactus" placeholder="Name" type="type" name="Name">
            </div>
            <div class="col-md-12">
              <input class="contactus" placeholder="Email" type="type" name="Email">
            </div>
            <div class="col-md-12">
              <input class="contactus" placeholder="Phone Number" type="type" name="Phone Number">
            </div>
            <div class="col-md-12">
              <textarea class="textarea" placeholder="Message" type="type" Message="Name">Message</textarea>
            </div>
            <div class="col-md-12">
              <button class="send_btn">Send</button>
            </div>
          </div>
        </form>
      </div>
<!--      <div class="col-md-6 padding_left0">
        <div class="map_main">
          <div class="map-responsive">
            <iframe src="https://www.google.com/maps/embed/v1/place?key=AIzaSyA0s1a7phLN0iaD6-UE7m4qP-z21pH0eSc&amp;q=Eiffel+Tower+Paris+France" width="600" height="463" frameborder="0" style="border:0; width: 100%;" allowfullscreen=""></iframe>
          </div>
        </div>
      </div>
-->    </div>
  </div>
</div>
<!-- end contact -->
<!--  footer -->
<footer>
  <div class="footer">
    <div class="container">
      <div class="row">
        <div class="col-md-8 offset-md-2">
          <div class="newslatter">
            <h4>Subscribe Our Newsletter</h4>
            <form class="bottom_form">
              <input class="enter" placeholder="Enter your email" type="text" name="Enter your email">
              <button class="sub_btn">subscribe</button>
            </form>
          </div>
        </div>
        <div class="col-sm-12">
          <div class=" border_top1"></div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-4">
          <h3>QUICK LINKS</h3>
          <ul class="link_menu">
            <li><a href="#">Home</a></li>
            <li><a href="#"> About</a></li>
            <li><a href="#">Classes</a></li>
            <li><a href="#">Yoga</a></li>
            <li><a href="#">pricing</a></li>
            <li><a href="#">Contact Us</a></li>
          </ul>
        </div>
        <div class=" col-md-3">
          <h3>TOP Yoga</h3>
          <p class="many">
            There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humou
          </p>
        </div>
        <div class="col-lg-3 offset-mdlg-2     col-md-4 offset-md-1">
          <h3>Contact </h3>
          <ul class="conta">
            <li><i class="fa fa-map-marker" aria-hidden="true"></i> Location</li>
            <li> <i class="fa fa-envelope" aria-hidden="true"></i><a href="#"> demo@gmail.com</a></li>
            <li><i class="fa fa-mobile" aria-hidden="true"></i> Call : +01 1234567890</li>
          </ul>
          <ul class="social_icon">
            <li><a href="#"><i class="fa fa-facebook" aria-hidden="true"></i></a></li>
            <li><a href="#"><i class="fa fa-twitter" aria-hidden="true"></i></a></li>
            <li><a href="#"><i class="fa fa-linkedin" aria-hidden="true"></i></a></li>
            <li><a href="#"><i class="fa fa-instagram" aria-hidden="true"></i></a></li>
          </ul>
        </div>
      </div>
    </div>
    <div class="copyright">
      <div class="container">
        <div class="row">
          <div class="col-md-10 offset-md-1">
            <p>© 2019 All Rights Reserved. Design by <a href="https://html.design/"> Free Html Templates</a></p>
          </div>
        </div>
      </div>
    </div>
  </div>
</footer>
<!-- end footer -->
<!-- Javascript files-->
<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.bundle.min.js"></script>
<script src="js/jquery-3.0.0.min.js"></script>
<!-- sidebar -->
<script src="js/jquery.mCustomScrollbar.concat.min.js"></script>
<script src="js/custom.js"></script>
</body>
</html>
<?php

if (isset($debeetleException)) {
    printf(
        "<div style=\"z-index: 100500; position: fixed; top: 2px; left: 3px; display: block; padding: 2px 3px; border: 1px dashed red; color: red; background: #EEE;\"><b>%s: %s</b> at %s(%d)\n<pre>%s</pre></div>\n",
        get_class($debeetleException),
        $debeetleException->getMessage(),
        $debeetleException->getFile(),
        $debeetleException->getLine(),
        $debeetleException->getTraceAsString()
    );
    unset($debeetleException);
} else {
    /**
     * @var deepeloper\Debeetle\DebeetleInterface $debeetle
     */
    $debeetle = d::getInstance();
    if ($debeetle) {
        $settings = $debeetle->getSettings();
        d::t("environment|includedFiles");
        d::dump(
        "debeetle" === $settings['bench']['includedFiles']['exclude']
            ? $debeetle->getExternalIncludedFiles()
            : get_included_files()
        );
        echo $debeetle->getView()->get();
    }
    unset($debeetle, $settings);
}
?>
</body>
</html>
