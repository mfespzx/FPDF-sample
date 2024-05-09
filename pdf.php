<?php
//include_once('../../../wp-load.php');
require(dirname(__FILE__).'/fpdf/mbfpdf.php');
require(dirname(__FILE__).'/fpdf/mbfpdi.php');

class Bukken_pdf
{
	private $number;
	private $name;
	private $pref;
	private $addr;
	private $transport;
	private $floor;
	private $tsubo;

	function __construct($estate) {
        /*
        <input type="hidden" name="number" value="$estate">
            <input type="hidden" name="name" value="$name">
            <input type="hidden" name="pref" value="$pref">
            <input type="hidden" name="addr" value="$addr">
            <input type="hidden" name="transport" value="$transport">
            <input type="hidden" name="transport2" value="$transport2">
            <input type="hidden" name="floor" value="$floor">
            <input type="hidden" name="space" value="$space">
            <input type="hidden" name="chinryou" value="$chinryou">

            <input type="hidden" name="kyoeki" value="$kyoeki">
            <input type="hidden" name="kyoeki2" value="$kyoeki2">
            <input type="hidden" name="hoshokin" value="$hoshokin">
            <input type="hidden" name="syokyaku" value="$syokyaku">
            <input type="hidden" name="reikin" value="$reikin">
            <input type="hidden" name="kikan" value="$kikan">
            <input type="hidden" name="genkyo" value="$genkyo">
            <input type="hidden" name="keitai" value="$keitai">
            <input type="hidden" name="hikiwatashi" value="$hikiwatashi">
            <input type="hidden" name="zosaku" value="">*/

        $posts = get_posts(array('name'=>'estate-' . $estate));
        $this->id = $id = $posts[0]->ID;

        $this->number = get_post_meta($id, "物件番号", true);

        $this->name = get_post_meta($id, "建物名", true);

        $this->pref = get_post_meta($id, "都道府県名", true);

        $addr1 = get_post_meta($id, "所在地名1", true);
        $addr2 = get_post_meta($id, "所在地名2", true);
        $addr3 = get_post_meta($id, "所在地名3", true);
        $this->addr = $this->pref.$addr1.$addr2.$addr3;

        $transport_1 = get_post_meta($id, "駅名（1）", true);
        $transport_2 = get_post_meta($id, "徒歩（分）1（1）",true);
        $this->transport = "";
        if($transport_1) {
            $this->transport = $transport_1. " 徒歩";
            if($transport_2) {
                $this->transport .= $transport_2. "分";
            }
        }

        $transport2_1 = get_post_meta($id, "バス（1）",true);
        $transport2_2 = get_post_meta($id, "バス停名称（1）",true);
        $transport2_3 = get_post_meta($id, "停歩（分）（1）",true);
        $this->transport2 = "";
        if($transport2_1) {
            $this->transport2 = $transport2_1. " " ;
            if($transport2_2) {
                $this->transport2 .= $transport2_2. "下車";
                if($transport2_3) {
                    $this->transport2 .= $transport2_3. "分";
                }
            }
        }

        $this->floor = get_post_meta($id, "所在階", true);
        $this->space = get_post_meta($id, "使用部分面積", true);
        $this->chinryou = get_post_meta($id, "賃料", true);
        $this->kyoeki = get_post_meta($id, "管理費", true);
        $this->kyoeki2 = get_post_meta($id, "共益費", true);
        $this->hoshokin = get_post_meta($id, "保証金2（ヶ月）", true);
        $this->syokyaku = get_post_meta($id, "償却月数", true);
        $this->reikin = get_post_meta($id, "礼金2（ヶ月）", true);
        $this->kikan = get_post_meta($id, "契約期間", true);
        $this->genkyo = get_post_meta($id, "現況", true);

		/*$this->id = $_POST['number'];
		$this->name = $_POST['name'];
		$this->pref = $_POST['pref'];
		$this->addr = $_POST['addr'];
		$this->transport = $_POST['transport'];
		$this->transport2 = $_POST['transport2'];
		$this->floor = str_replace("階建", "", $_POST['floor']);
		$this->space = $_POST['space'];
		$this->chinryou = $_POST['chinryou'];
		$this->kyoeki = $_POST['kyoeki'];
		$this->kyoeki2 = $_POST['kyoeki2'];
		$this->hoshokin = $_POST['hoshokin'];
		$this->syokyaku = $_POST['syokyaku'];
		$this->reikin = $_POST['reikin'];
		$this->kikan = $_POST['kikan'];
		$this->genkyo = $_POST['genkyo'];
		$this->keitai = $_POST['keitai'];
		$this->hikiwatashi = $_POST['hikiwatashi'];
//		$this->zosaku = $_POST['zosaku'];

        */
		$this->make_pdf();
	}


	function make_pdf() {
		$pdf = new fpdi();
		$pagecount = $pdf->setSourceFile(dirname(__FILE__).'/template.pdf');
		$tplidx = $pdf->ImportPage($pagecount);$pdf->AddMBFont(GOTHIC, 'SJIS');
		$pdf->Open();
		$pdf->addPage('L');
		$pdf->useTemplate($tplidx, 0, 0, 297, 210);
		//$pdf->Output($this->id. ".pdf", 'D');
		$pdf->AddMBFont(PGOTHIC, 'SJIS');
		$pdf->SetFont(GOTHIC,'', 14);
		$pdf->Text(19, 20.6, mb_convert_encoding($this->number, 'SJIS', 'UTF-8'));
		$pdf->SetFont(GOTHIC,'', 28);
		$pdf->Text(50, 19, mb_convert_encoding($this->name, 'SJIS', 'UTF-8'));
		$pdf->SetFont(GOTHIC,'', 11);
		$pdf->Text(20, 38, mb_convert_encoding('《物件概要》', 'SJIS', 'UTF-8'));

		$x = $pdf->getX() + 145;
		$y = $pdf->getY() + 28;

		global $wpdb;
		$pref_cd = $wpdb->get_var($wpdb->prepare("
			SELECT		pref_cd
			FROM		pref
			WHERE		pref_name = %s",
			$this->pref
		));
        $path = ABSPATH."/bukken-map/" .$pref_cd. "/" .$this->number. ".png";
        if(!file_exists($path)) {
            file_get_contents("http://".$_SERVER['HTTP_HOST']."/wp/wp-content/plugins/bukken-map/get_googlemap.php?postid=".$this->id);
        }

        if(file_exists($path)) {
            $pdf->Image($path, $x, $y, 130.0);
        }

		$pdf->SetFont(GOTHIC, '', 10);
		$cy = 47;
		$number = $this->id;

		if ($this->name) {
			$this->name = '◆物 件 名／' .$this->name;
			$pdf->Text(19, $cy, mb_convert_encoding($this->name, "SJIS", "UTF-8"));
			$cy = $cy + 5;
		}
		if ($this->addr) {
			$this->addr = '◆所 在 地／' .$this->addr;
			$pdf->Text(19, $cy, mb_convert_encoding($this->addr, "SJIS", "UTF-8"));
			$cy = $cy + 5;
		}
		if ($this->transport) {
			$this->transport = '◆交　　通／' .$this->transport;
			$pdf->Text(19, $cy, mb_convert_encoding($this->transport, "SJIS", "UTF-8"));
			$cy = $cy + 5;
		}
		if (preg_match("/下車(.+?)分/", $this->transport2)) {
			$pdf->Text(40, $cy, mb_convert_encoding($this->transport2, "SJIS", "UTF-8"));
			$cy = $cy + 5;
		}
		if ($this->floor) {
			$this->floor = '◆階　　数／' .$this->floor . ((strpos("建", $this->floor)!==false) ? "階" : "");
			$pdf->Text(19, $cy, mb_convert_encoding($this->floor, "SJIS", "UTF-8"));
			$cy = $cy + 5;
		}
		if ($this->space) {
			$tsubo = round(($this->space * 0.3025), 2);
			$this->space = '◆面　　積／'.$tsubo."坪・".$this->space."ｍ2";
			$pdf->Text(19, $cy, mb_convert_encoding( $this->space, "SJIS", "UTF-8"));

			$cy = $cy + 5;
		}

		$pdf->SetFont(GOTHIC,'', 11);
		$cy = $cy + 10;
		$pdf->Text(20, $cy, mb_convert_encoding('《募集条件》', 'SJIS', 'UTF-8'));
		$pdf->SetFont(GOTHIC, '', 10);
		$cy = $cy + 9;

		//$this->chinryou = number_format($this->chinryou);
		//$this->kyoeki = number_format($this->kyoeki);

		if ($this->chinryou) {
			$value_sepa = explode(",", number_format($this->chinryou));
			foreach ($value_sepa as $value) {
				$chinryou .= $value. ",";
			}
			$chinryou = preg_replace("/\,$/", "", $chinryou);
			$pdf->Text(19, $cy, mb_convert_encoding(("◆賃　　料／" .$chinryou. "円 (税別)"), "SJIS", "UTF-8"));
			$cy = $cy + 5;
		}
		if ($this->kyoeki || $this->kyoeki2) {
			$kyoeki = $kyoeki + $kyoeki2;
			$value_sepa = explode(",", number_format($this->kyoeki));
			foreach ($value_sepa as $value) {
				$kyoeki .= $value. ",";
			}
			$kyoeki = preg_replace(array("/^0/", "/\,$/"), "", $kyoeki);
			$pdf->Text(19, $cy, mb_convert_encoding("◆共 益 費／" .$kyoeki. "円 (税別)", "SJIS", "UTF-8"));
			$cy = $cy + 5;
		}
		if ($this->hoshokin) {
			$pdf->Text(19, $cy, mb_convert_encoding("◆保 証 金／" .$this->hoshokin. "ヶ月", "SJIS", "UTF-8"));
			$cy = $cy + 5;
		} else {
			#$pdf->Text(19, $cy, mb_convert_encoding("◆保 証 金／相談", "SJIS", "UTF-8"));
			#$cy = $cy + 5;
		}
/*
		$pdf->Text(9, $cy, mb_convert_encoding("◆看 板 料／賃料に含む", "SJIS", "UTF-8"));
		$cy = $cy + 5;
*/
		if ($this->syokyaku) {
			$pdf->Text(19, $cy, mb_convert_encoding("◆償　　却／" .$this->syokyaku. "ヶ月", "SJIS", "UTF-8"));
			$cy = $cy + 5;
		}
		if ($this->reikin) {
			$pdf->Text(19, $cy, mb_convert_encoding("◆礼　　金／" .$this->reikin. "ヶ月", "SJIS", "UTF-8"));
			$cy = $cy + 5;
		}
		if ($this->kikan) {
			$pdf->Text(19, $cy, mb_convert_encoding("◆契約期間／" .$this->kikan. "ヶ月", "SJIS", "UTF-8"));
			$cy = $cy + 5;
		} else {
			//$pdf->Text(19, $cy, mb_convert_encoding("◆契約期間／相談", "SJIS", "UTF-8"));
			//$cy = $cy + 5;
		}
		/*if ($this->genkyo) {
			$pdf->Text(19, $cy, mb_convert_encoding("◆現　　況／" .$this->genkyo, "SJIS", "UTF-8"));
			$cy = $cy + 5;
		}
		if ($this->keitai) {
			$pdf->Text(19, $cy, mb_convert_encoding("◆契約形態／" .$this->keitai, "SJIS", "UTF-8"));
			$cy = $cy + 5;
		}*/
		/* else {
			$pdf->Text(9, $cy, mb_convert_encoding("◆契約形態／相談", "SJIS", "UTF-8"));
			$cy = $cy + 5;
		}*/
		/*if ($this->hikiwatashi) {
			$pdf->Text(19, $cy, mb_convert_encoding("◆引渡時期／" .$this->hikiwatashi, "SJIS", "UTF-8"));
			$cy = $cy + 5;
		}*/
		/* else {
			$pdf->Text(9, $cy, mb_convert_encoding("◆引渡時期／相談", "SJIS", "UTF-8"));
			$cy = $cy + 5;
		}*/
		/*
		if ($this->zosaku) {
			$pdf->Text(9, $cy, mb_convert_encoding("◆造 作 代／" .$this->zosaku, "SJIS", "UTF-8"));
			$cy = $cy + 5;
		} else {
			$pdf->Text(9, $cy, mb_convert_encoding("◆造 作 代／なし", "SJIS", "UTF-8"));
			$cy = $cy + 5;
		}
		*/

		$pdf->SetFont(GOTHIC,'', 11);
		$cy = $cy + 10;
		$pdf->Text(20, $cy, mb_convert_encoding('《 その他 》', 'SJIS', 'UTF-8'));
		$pdf->SetFont(GOTHIC, '', 6);
		$cy = $cy + 5;
		$pdf->Text(19, $cy, mb_convert_encoding("この物件情報は賃貸条件の一部を抜粋したものを元に提供しています。", "SJIS", "UTF-8"));
		$pdf->Text(19, $cy + 3, mb_convert_encoding("実際の募集条件とは異なる場合がございます。", "SJIS", "UTF-8"));

		$pdf->Output("bukken_".$this->id. ".pdf", 'D');
	}

}
//$bukken_pdf = new Bukken_pdf($_GET['estate']);
?>
