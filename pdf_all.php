<!DOCTYPE html>
<!--[if IE 8]>
<html xmlns="http://www.w3.org/1999/xhtml" class="ie8 wp-toolbar"  lang="ja">
<![endif]-->
<!--[if !(IE 8) ]><!-->
<html xmlns="http://www.w3.org/1999/xhtml" class="wp-toolbar"  lang="ja">
<!--<![endif]-->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>物件PDF一覧</title>
</head>
<body>
<?php
include_once('../../../wp-load.php');

class Pdf_all
{

	function __construct($get){
		$this->get_pdf($get);
	}


	private function get_pdf($get) {
		global $wpdb;

        if(!empty($get['date'])) {
            $year = (int)substr($get['date'], 0, 4);
            $month = (int)substr($get['date'], 4, 2);
            $day = (int)substr($get['date'], 6, 2);


?>
<div class="top">
<h1><?php echo $year; ?>年<?php echo $month; ?>月<?php echo $day; ?>日の物件ご案内です</h1>
<p>※ 周辺地図は物件によっては正確な位置を示さない場合があります。</p>
</div>
<?php
        }

		$bukken_id_array = explode("/", $get['estate']);
		$bukken_id_array = array_filter($bukken_id_array);
		foreach ($bukken_id_array as $key => $estate) {
            if(!$estate) continue;
			$id = get_posts(array('name'=>'estate-' . $estate));
			$id = $id[0]->ID;

            $number = get_post_meta($id, "物件番号",true);

            if(!$number)continue;

			$name = get_post_meta($id, 建物名);
			$name = $name[0];
			$pref = get_post_meta($id, 都道府県名);
			$pref = $pref[0];
			$pref_cd = $wpdb->get_col($wpdb->prepare("
				SELECT		pref_cd
				FROM		pref
				WHERE		pref_name = %s",
				$pref
			));
			$pref_cd = $pref_cd[0];
			$addr1 = get_post_meta($id, 所在地名1);
			$addr2 = get_post_meta($id, 所在地名2);
			$addr3 = get_post_meta($id, 所在地名3);
			$addr = $pref.$addr1[0].$addr2[0].$addr3[0];
			$transport = get_post_meta($id, 駅名（1）);
			$transport_2 = get_post_meta($id, 徒歩（分）1（1）);
			$transport = $transport[0]. "駅 徒歩" .$transport_2[0]. "分";
			$transport2 = get_post_meta($id, バス（1）);
			$transport2_2 = get_post_meta($id, バス停名称（1）);
			$transport2_3 = get_post_meta($id, 停歩（分）（1）);
			$transport2 = $transport2[0]. " " .$transport2_2[0]. "下車" .$transport2_3[0]. "分";
			$floor = get_post_meta($id, 所在階);
			$floor = $floor[0];
			$space = get_post_meta($id, 使用部分面積);
			$space = $space[0];
			$chinryou = get_post_meta($id, 坪単価);
			$chinryou = $chinryou[0];
			$kyoeki = get_post_meta($id, 管理費);
			$kyoeki = $kyoeki[0];
			$kyoeki2 = get_post_meta($id, 共益費);
			$kyoeki2 = $kyoeki2[0];
			$hoshokin = get_post_meta($id, 保証金2（ヶ月）);
			$hoshokin = $hoshokin[0];
			$syokyaku = get_post_meta($id, 償却月数);
			$syokyaku = $syokyaku[0];
			$reikin = get_post_meta($id, 礼金2（ヶ月）);
			$reikin = $reikin[0];
			$kikan = get_post_meta($id, 契約期間);
			$kikan = $kikan[0];
			$genkyo = get_post_meta($id, 現況);
			$genkyo = $genkyo[0];
			$keitai = get_post_meta($id, 取引態様);
			$keitai = $keitai[0];
			$hikiwatashi = get_post_meta($id, 入居時期);
			$hikiwatashi = $hikiwatashi[0];
            if($key) {
                echo "<div style='page-break-before:always;' >";
            } else {
                echo "<div>";
            }
			echo "<div class=\"header\">\n";
			echo "<div class=\"estate\"><strong>物件番号</strong><br>" .$estate. "</div>";
			echo "<div class=\"build\">" .$name. "</div>\n<br clear=\"all\">";
			echo "</div>\n";
			echo "<div class=\"main_wrap\">\n";
			echo "<div class=\"descrpt\">\n";
			echo '<h3>《物件概要》</h3>';
			echo "<dl>\n";
			echo '<dt>◆物件名／</dt>';
			echo "<dd>" .$name. "</dd>\n";
			echo '<dt>◆所在地／</dt>';
			echo "<dd>" .$addr. "</dd>\n";
			echo '<dt>◆交　通／</dt>';
			echo "<dd>" .$transport. "";
			if (preg_match("/下車(.+?)分/", $transport2)) {
				echo "<br>" .$transport2. "</dd>\n";
			}
			echo '<dt>◆階　数／</dt>';
			echo "<dd>" .$floor. " 階</dd>\n";
			echo '<dt>◆面　積／</dt>';
			echo "<dd>" .$space. "平米 (約" .(round($space * 0.3025, 2)). "坪)</dd>\n";
			echo "<br clear=\"all\">\n</dl>\n";

			echo '<h3>《募集条件》</h3>';
			echo "<dl>\n";
			echo '<dt>◆賃　料／</dt>';
			echo "<dd>坪単価" .number_format($chinryou). "円 (税別)</dd>\n";
			echo '<dt>◆共益費／</dt>';
			echo "<dd>坪単価" .number_format($kyoeki + $kyoeki2). "円 (税別)</dd>\n";
			echo '<dt>◆保証金／</dt>';
			if (!$kikan) {
				$hoshokin = '相談';
			} else {
				$hoshokin = $hoshokin. "ヶ月";
			}
			echo "<dd>" .$hoshokin. "</dd>\n";
			if ($syokyaku) {
				echo '<dt>◆償　却／</dt>';
				echo "<dd>" .$syokyaku. " ヶ月</dd>\n";
			}
			if ($reikin) {
				echo '<dt>◆礼　金／</dt>';
				echo "<dd>" .$reikin. " ヶ月</dd>\n";
			}
			echo '<dt>◆契約期間／</dt>';
			if (!$kikan) {
				$kikan = '相談';
			} else {
				$kikan = $kikan. "ヶ月";
			}
			echo "<dd>" .$kikan. " </dd>\n";
			echo '<dt>◆現 況／</dt>';
			if (!$genkyo) {
				$genkyo = '空き';
			} else {
				$genkyo = $genkyo. "ヶ月";
			}
			echo "<dd>" .$genkyo. " </dd>\n";
			if ($reikin) {
				echo '<dt>◆契約形態／</dt>';
				echo "<dd>" .$keitai. " ヶ月</dd>\n";
			}
			if ($hikiwatashi) {
				echo '<dt>◆引渡時期／</dt>';
				echo "<dd>" .$hikiwatashi. " ヶ月</dd>\n";
			}
			echo "<br clear=\"all\">\n</dl>\n";
			echo '<h3>《その他》</h3>';
			echo "<p>この物件情報は賃貸条件の一部を抜粋したものを元に提供しています。<br>実際の募集条件とは異なる場合がございます。</p>\n";
			echo "</div>\n";

			echo "<div class=\"bukken_map\">\n";
			echo "<img src=\"../../../bukken-map/" .$pref_cd. "/" .$estate. ".png\" alt=\"" .$build. "マップ\" width=350>\n";
			echo "</div>\n<br clear=\"all\">\n";
            echo "<img src=\"".get_template_directory_uri()."/images/pdficon_small.png\" alt=\"pdf\" />"."<a href=\"/bukken_{$number}.pdf\">この物件のPDFをダウンロード</a>";
			echo "</div><!--main wrap-->\n";

            echo "</div>";
		}


	}

}
$pdf_all = new Pdf_all($_GET);
?>
<style>
	body {
		font-size: 12px;
		line-height: 140%;
		color: #444;
	}

    .top {
        width: 690px;
        margin: 0 auto;
    }

	.header {
		width: 690px;
		margin: 0 auto;
		border: 1px solid #ccc;
	}
	.estate {
		width: 150px;
		margin: 20px 20px;
		font-size: 18px;
		float: left;
	}
	.build {
		width: 300px;
		padding-top: 30px;
		float: left;
		font-size: 32px;
		text-align: left;
	}
	.main_wrap {
		width: 700px;
		margin: 30px auto 30px auto;
	}
	.descrpt {
		float: left;
		width: 340px;
		font-size: 14px;
	}
	.descrpt h3 {
		margin-left: 20px;
	}
	.descrpt dl {
		margin: 0 0 30px 10px;
	}
	.descrpt dt, .descrpt dd {
		float: left;
		margin: 0;
		padding: 0;
	}
	.descrpt dt {
		clear: both;
		width: 90px;
	}
	.descrpt p {
		font-size: 12px;
	}
	.bukken_map {
		float: left;
		padding-top: 0px;
	}
</style>
</body>
</html>