<?php

/**
 *  @author: Graziani Arciprete - psymics(at)gmail(dot)com
 *  @description: Validar o login e senha enviados pelo App ControlTracker (apenas para cliente)
 */

/**
 *	Função para protejer do SQL Inject
 */

function protejeInject($str)
{
	$sql = preg_replace("/( from |select|insert|delete|where|drop table|show tables|#|\*|--|\\\\)/", "", $str);
	$sql = trim($sql);
	$sql = strip_tags($sql);
	$sql = (get_magic_quotes_gpc()) ? $sql : addslashes($sql);
	return $sql;
}

function ndistance($lat1, $lon1, $lat2, $lon2, $unit)
{

	$theta = $lon1 - $lon2;
	$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
	$dist = acos($dist);
	$dist = rad2deg($dist);
	$miles = $dist * 60 * 1.1515;
	$unit = strtoupper($unit);

	if ($unit == "K") {
		return ($miles * 1.609344);
	} else if ($unit == "N") {
		return ($miles * 0.8684);
	} else {
		return $miles;
	}
}

$login = protejeInject($_POST['v_login']);
if (!$login) {
	$login = protejeInject($_GET['v_login']);
}

require_once("config.php");

$con 		= mysqli_connect($DB_SERVER, $DB_USER, $DB_PASS) or die("Não foi possivel conectar ao Mysql" . mysqli_error());
mysqli_select_db($con, $DB_NAME);
$auth_user = strtolower($login);

$sql =
	"SELECT
					    CAST(a.id AS DECIMAL(10,0)) as id_cliente
				   FROM cliente a
				  WHERE (a.email = '" . $auth_user . "' OR a.apelido = '" . $auth_user . "')

				  LIMIT 1";



$stm = mysqli_query($con, $sql);
$rs = mysqli_fetch_array($stm);
$id_cliente = $rs['id_cliente'];

//echo "id cliente = $id_cliente";

?>
<link rel="stylesheet" href="../../ctracker/plugins/fontawesome-free/css/all.min.css">

<style>
	.flex-box {
		display: flex;
		align-items: center;
		justify-content: center;
	}
</style>
<div class="flex-box esconder">

	<div class="content-box ">
		<button onclick="tracarMapa()" class="btn btn-light" style="margin:4%;"><strong style="color:#696;"> <i class="fas fa-map-marked-alt"></i> </strong>ROTA NO
			MAPA</button>
	</div>
</div>
<button onclick="fullscreen()" class="btn  btn-outline-primary" style="margin-left: 1% !important;" id="btn_fullscreen"><i class="fas fa-compress"></i></button>
<a name="mostaMapa"></a>


<div id="mapa" style="height: 450px;
    width: 100%;
    min-width:450px;
    margin: 0px auto;
    position: relative;
    overflow: hidden;
    border: solid 2px #ccc; display:none;"></div>
<div class="table-responsive">
	<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCK68SWDM0H4gc23n_8eK7gFbyCgtYKCYk"></script>
	<script>
		var posicoes = [];
	</script>
	<table border="1" class="table table-striped table-hover table-bordered " id="tabelaMapa">
		<thead>
			<tr>
				<th>#</th>

				<th>Data GPS</th>
				<th>Endereço</th>
				<th>Velocidade</th>
				<th>Ligado</th>
				<th>Placa</th>
				<th>Km</th>
			</tr>
		</thead>
		<?php


		$q_tabela = "gprmc_old";

		$dataInicial = $_POST['v_data_ini'];
		$dataFinal = $_POST['v_data_fim'];

		$horaInicial = $_POST['v_hora_ini'];
		$horaFinal = $_POST['v_hora_fim'];

		$df = date("Y-m-d");
		$dStart = new DateTime($dataInicial);
		$dEnd  	= new DateTime($df);
		$dDiff 	= $dStart->diff($dEnd);
		// echo $dDiff->format('%R'); // use for point out relation: smaller/greater
		$dias_dif = $dDiff->days;

		if (strtotime($dataInicial) > strtotime("26-01-2021")) {
			$q_tabela = "gprmc";
		}




		//echo "Tabela: $q_tabela".strtotime("26-01-2021" );

		$imei = $_POST['v_veiculo'];
		if ($imei == 'TODOS') {
			$stm_idbem = mysqli_query($con, "select id, name, imei from bem where cliente = '$id_cliente'");

			$idbem = '0';

			while ($rs_idbem = mysqli_fetch_assoc($stm_idbem)) {
				$idbem .= ',' . $rs_idbem['id'];
				$placa[$rs_idbem['imei']] = $rs_idbem['name'];
			}
		} else {
			$stm_idbem = mysqli_query($con, "select id, name, imei from bem where imei = '$imei'");
			$rs_idbem = mysqli_fetch_assoc($stm_idbem);
			$idbem = $rs_idbem['id'];
			$placa[$rs_idbem['imei']] = $rs_idbem['name'];
		}

		$sql = "
		select
						( select name from bem b where b.imei = a.imei  ) as placa
						, ( select modelo_rastreador from bem b where b.imei = a.imei  ) as modelo_rastreador
						, ( select c.nome from bem b, cliente c where b.cliente = c.id and b.imei = a.imei  ) as cliente
						, date_format(date,'%d/%m/%Y %H:%i:%s') as data
						, date_format(data_comunica,'%d/%m/%Y %H:%i:%s') as data_comunica
						, imei
						, latitudeDecimalDegrees
						, latitudeHemisphere
						, longitudeDecimalDegrees
						, longitudeHemisphere
						, km_rodado
						, address
						, speed
						, rpm
						, converte
						, ligado
						, s1 as bloqueado
						, voltagem_bateria
						, infotext
						, motorista
						, TIMESTAMPDIFF(MINUTE,date,data_comunica) as minutocomunica
		from $q_tabela a
		where id_bem in ( $idbem )
		and date between '$dataInicial $horaInicial' and '$dataFinal $horaFinal'
			and speed * 1.852 >= '$consulta'
		order by date desc

		";
		//echo $sql;
		$stm = mysqli_query($con, $sql);
		$aux = 0;

		while ($rs = mysqli_fetch_array($stm)) {
			$aux++;

			if ($rs['latitudeDecimalDegrees'] > 0) {
				strlen($rs['latitudeDecimalDegrees']) == 9 && $rs['latitudeDecimalDegrees'] = '0' . $rs['latitudeDecimalDegrees'];
				$g = substr($rs['latitudeDecimalDegrees'], 0, 3);
				$d = substr($rs['latitudeDecimalDegrees'], 3);
				$latitudeDecimalDegrees = $g + ($d / 60);
				$rs['latitudeHemisphere'] == "S" && $latitudeDecimalDegrees = $latitudeDecimalDegrees * -1;

				strlen($rs['longitudeDecimalDegrees']) == 9 && $rs['longitudeDecimalDegrees'] = '0' . $rs['longitudeDecimalDegrees'];
				$g = substr($rs['longitudeDecimalDegrees'], 0, 3);
				$d = substr($rs['longitudeDecimalDegrees'], 3);
				$longitudeDecimalDegrees = $g + ($d / 60);
				$rs['longitudeHemisphere'] == "W" && $longitudeDecimalDegrees = $longitudeDecimalDegrees * -1;

				$latitudeDecimalDegrees = substr($latitudeDecimalDegrees, 0, 10);
				$longitudeDecimalDegrees = substr($longitudeDecimalDegrees, 0, 10);
			} else {
				$latitudeDecimalDegrees = substr($rs['latitudeDecimalDegrees'], 0, 10);
				$longitudeDecimalDegrees = substr($rs['longitudeDecimalDegrees'], 0, 10);
			}
			$address = utf8_encode($rs['address']);
			$speed = $rs['speed'];
			$escreveSN = $rs['ligado'] == 'S' || $rs['speed'] > 0 ? 'Sim' : 'Não';

			echo  "<tr>

					<td style='background-color:" . $bg . ";'>" . $aux . " </td>
					<td style='background-color:" . $bg . ";'>" . $rs[data] . " </td>
					<td style='background-color:" . $bg . ";'>" . $address . "</td>
					<td style='background-color:" . $bg . ";'>" . floor($speed) . " Km/h" . " </td>
					<td style='background-color:" . $bg . ";'>" . $escreveSN . "</td>
					<td style='background-color:" . $bg . ";'>" . $rs[placa] . "</td>
					 ";

			if ($u_lat) {

				$dist_s = ndistance($u_lat, $u_lng, $latitudeDecimalDegrees, $longitudeDecimalDegrees, "k");
				//echo "dist_s = $dist_s <br> ";
				if (is_nan($dist_s)) {
					$dist_s = 0;
				}
				$total_km += $dist_s;
				echo  "<td style='background-color:" . $bg . ";'>  " . number_format($total_km, 2) . "km </td> ";
			} else {
				echo  "<td style='background-color:" . $bg . ";'> - </td> ";
			}

			echo  '

<td><a href="http://maps.google.com/maps?q=' . $latitudeDecimalDegrees . ',' .  $longitudeDecimalDegrees . '" target="_blank" class="external" ><img src="https://api.ctracker.com.br/imagens/mapa_globo.png"  title="Veiculo Ligado"> </a></td>

				</tr>';

		?>
			<script>
				posicoes.push({
					'data': '<?php echo $rs[data]; ?>',
					'data_comunica': '<?php echo $rs[data_comunica]; ?>',
					'minutocomunica': '<?php echo $rs[minutocomunica]; ?>',
					'status': '',
					'ignicao': '<?php echo $escreveSN; ?>',
					'evento': '<?php echo $rs[infotext]; ?>',
					'voltagem_bateria': '<?php echo $rs[voltagem_bateria]; ?>',
					'address': '<?php echo $address; ?>',

					'placa': '<?php echo $rs[placa]; ?>',
					'velocidade': '<?php echo $rs[speed]; ?>',
					'rpm': '<?php echo $rs[rpm]; ?>',
					'km_rodado': '<?php echo $rs[km_rodado]; ?>',
					'cliente': '<?php echo $rs[cliente]; ?>',
					'motorista': '<?php echo $rs[motorista]; ?>',
					'modelo_rastreador': '<?php echo $rs[modelo_rastreador]; ?>',
					'mapa': '',

					'lat': '<?php echo $latitudeDecimalDegrees; ?>',
					'lng': '<?php echo $longitudeDecimalDegrees; ?>',


				});
			</script>
		<?php

			$u_lat =  $latitudeDecimalDegrees;
			$u_lng =  $longitudeDecimalDegrees;
		}

		?>
	</table>
</div>

<script>
	var fs;

	function fullscreen() {
		document.body.requestFullscreen();
		$("#mapa").css({"height": "750px","margin-left": "-5%"});
		$("#btn_fullscreen").css({"margin-top": "-10%","position":"absolute"});
		if (fs) {
			$(".esconder").show();
			fs = false;
		} else {
			$(".esconder").hide();
			fs = true;
		}
	}

	function tracarMapa() {

		$("#btn_fullscreen").show();
		$("#mapa").show();
		$("#tabelaMapa").hide();
		$("#mapa").css("height: 750px;");
		var latlng = new google.maps.LatLng(-10.947765, -37.072953); //DEFINE A LOCALIZAÇÃO EXATA DO MAPA

		var myOptions = {
			zoom: 15,
			center: latlng,
			mapTypeId: google.maps.MapTypeId.ROADMAP
		};

		//CRIANDO O MAPA
		var_map = new google.maps.Map(document.getElementById("mapa"), {
			zoom: 15,
			center: latlng,
			mapTypeControl: true,
			mapTypeControlOptions: {
				style: google.maps.MapTypeControlStyle.DROPDOWN_MENU,
				mapTypeIds: ["roadmap", "terrain","satellite"],
			},
			fullscreenControl: true,
			fullscreenControlOptions: {
				position: google.maps.ControlPosition.BOTTOM_LEFT,
			},
			zoomControl: true,
			zoomControlOptions: {
				position: google.maps.ControlPosition.LEFT_CENTER,
			},
			scaleControl: true,
			streetViewControl: true,
			streetViewControlOptions: {
				position: google.maps.ControlPosition.LEFT_TOP,
			},
		});

		markers = posicoes;
		arr = markers;
		var infowindow = new google.maps.InfoWindow();
		var datahora = [];
		var infotext = [];
		var velocidade = [];

		line = new google.maps.Polyline({
			map: var_map,
			icons: [{
				icon: {
					path: google.maps.SymbolPath.FORWARD_CLOSED_ARROW,
					strokeColor: '#0000ff',
					fillColor: '#0000ff',
					fillOpacity: 1
				},
				repeat: '100px',
				path: []
			}]
		});

		//for (i=0;i<arr.length;i++){
		var i = arr.length - 1;

		while (i > 0) {
			dados = arr[i];


			if (i == 0) {
				var iconeImg = 'https://api.ctracker.com.br/imagens/marker_end.png';
			} else if (i == (arr.length - 1)) {
				var iconeImg = 'https://api.ctracker.com.br/imagens/marker_start.png';
			} else {
				if (arr[i].ignicao == 'Sim') {
					var iconeImg = 'https://api.ctracker.com.br/imagens/ignicaoOn.png';
				} else {
					var iconeImg = 'https://api.ctracker.com.br/imagens/ignicaoOff.gif';
				}


			}

			path = line.getPath().getArray();
			latLng = new google.maps.LatLng(arr[i].lat, arr[i].lng);
			path.push(latLng);
			line.setPath(path);



			var_map.setCenter(latLng);

			const contentString =
				'<div id="content">' +
				'<div id="siteNotice">' +
				"</div>" +
				'<h3 id="firstHeading" class="firstHeading"></h3>' +
				'<div id="bodyContent">' +
				"<p><b>Data:</b>  " + arr[i].data +
				"<p><b>Velocidade:</b>  " + arr[i].velocidade + "KM/H" +
				"<p><b>Motorista:</b>  " + arr[i].motorista +
				"<p><b>Bateria:</b>  " + arr[i].voltagem_bateria +
				"<p><b>Evento:</b>  " + arr[i].evento +
				"<p><b>RPM:</b>  " + arr[i].rpm +
				"<p><b>Endereço:</b>  " + arr[i].address +
				"</p>" +
				"</div>" +
				"</div>";

			const infowindow = new google.maps.InfoWindow({
				content: contentString,
			});


			var marker = new google.maps.Marker({
				map: var_map,
				position: latLng,
				icon: iconeImg

			});

			marker.addListener("click", () => {
				infowindow.open(var_map, marker);
			});

			google.maps.event.addListener(marker, 'click', (function(marker, i) {
				return function() {
					infowindow.setContent(latLng);
					infowindow.open(var_map, marker);
				}
			})(marker, i));

			i--;
		};





	}
</script>