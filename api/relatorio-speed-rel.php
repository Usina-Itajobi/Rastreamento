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



$stm = mysqli_query($con, $sql) or die('Unable to execute query.');
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

<!-- <-- botao grafico -->
<!-- <button type="button" class="btn btn-outline-success" data-toggle="modal" data-target="#grafico" onclick="grafico()"><i class="fa fa-chart-bar"></i> Gráfico </button> -->

<div class="flex-box">

	<div class="content-box">

	</div>
</div>
<a name="mostaMapa"></a>


<div id="mapa" style="    height: 450px;
    width: 100%;
    min-width:450px;
    margin: 0px auto;
    position: relative;
    overflow: hidden;
    border: solid 2px #ccc; display:none;"></div>
<div class="table-responsive">
	<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCtw4_xnEOXxMhCBH8yJhleTeBTJB2_-RY"></script>
	<script>
		var posicoes = [];
	</script>
	<table border="1" class="table table-striped table-hover table-bordered " id="tabelaMapa">
		<thead>
			<tr>
				<th>Placa</th>
				<th>Velocidade</th>
				<th>Data GPS</th>
				<th>Endereço</th>
				<th>Ligado</th>
				<th>Km</th>
			</tr>
		</thead>
		<?php


		$q_tabela = "gprmc";

		$dataInicial = $_POST['v_data_ini'];
		$dataFinal = $_POST['v_data_fim'];

		$horaInicial = $_POST['v_hora_ini'];
		$horaFinal = $_POST['v_hora_fim'];
		$speed = $_POST['v_speed'];

		$df = date("Y-m-d");
		$dStart = new DateTime($dataInicio);
		$dEnd  	= new DateTime($df);
		$dDiff 	= $dStart->diff($dEnd);
		// echo $dDiff->format('%R'); // use for point out relation: smaller/greater
		$dias_dif = $dDiff->days;

		// $imeis1 = array();
		if ($dias_dif < 7) {
			$q_tabela = "gprmc_7dias";
		}

		//echo "Tabela: $q_tabela";

		$imei = $_POST['v_veiculo'];
		if ($imei == 'TODOS') {
			if ($id_cliente) {
				$stm_idbem = mysqli_query($con, "select id, name, imei from bem where cliente = '$id_cliente'");

				$idbem = '-1';

				while ($rs_idbem = mysqli_fetch_assoc($stm_idbem)) {
					$idbem .= ',' . $rs_idbem['id'];
					$placa[$rs_idbem['imei']] = $rs_idbem['name'];
				}
			} else {
				$idbem = '-1';

				$limitesql = "select COUNT(b.bem) as limite from grupo c, grupo_bem b where b.grupo = c.id and c.nome = '$login'";
				$limitesql = mysqli_query($con, $limitesql);
				$limitesql = mysqli_fetch_array($limitesql);

				$sqls = "SELECT DISTINCT a.id , a.name , b.latitudeDecimalDegrees , b.longitudeDecimalDegrees , a.tipo , b.address ,
				  DATE_FORMAT(b.date, '%d/%m/%Y %H:%i:%s') as dia , b.speed , b.ligado , a.imei , b.voltagem_bateria , a.bloqueado , a.ancora ,
				   b.km_rodado from bem a , loc_atual b where a.activated = 'S' and a.id in (select b.bem from grupo c, grupo_bem b 
				   where b.grupo = c.id and c.nome = '$login') LIMIT " . $limitesql['limite'] . "";

				// echo $sqls; die;
				$stm_idbem  = mysqli_query($con, $sqls);

				while ($rs_idbem = mysqli_fetch_assoc($stm_idbem)) {
					$idbem .= ',' . $rs_idbem['id'];
					$placa[$rs_idbem['imei']] = $rs_idbem['name'];
				}
			}
		} else if (strstr($imei, "g-")) {
			$imei1 = str_replace("g-", "", $imei);
			$stm_idbem = mysqli_query($con, "SELECT * FROM grupo_bem WHERE grupo = $imei1");

			$idbem = '-1';

			while ($rs_idbem = mysqli_fetch_assoc($stm_idbem)) {
				$idbem .= ',' . $rs_idbem['bem'];
				$placa[$rs_idbem['imei']] = $rs_idbem['descricao'];
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
						, TIMESTAMPDIFF(MINUTE,date,data_comunica) as minutocomunica
		from gprmc a
		where id_bem in ( $idbem )
		and date between '$dataInicial $horaInicial' and '$dataFinal $horaFinal'
			and speed  >= '$speed'
		order by data asc
	
		";
		//  echo $sql; die;
		// echo $imei;
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
			$add = explode(",", $address);

			$speed = $rs['speed'];
			$escreveSN = $rs['ligado'] == 'S' || $rs['speed'] > 0 ? 'Sim' : 'Não';

			echo  "<tr>
					<td style='background-color:" . $bg . ";'>" . $rs[placa] . "</td>
					<td style='background-color:" . $bg . ";'>" . floor($speed) . " Km/h" . " </td>
					<td style='background-color:" . $bg . ";'>" . $rs[data] . " </td>
					<td style='background-color:" . $bg . ";'>" . $add[0] . "</td>
					<td style='background-color:" . $bg . ";'>" . $escreveSN . "</td>
					 ";

			if ($rs['placa'] != null && $rs['imei'] != null) {
				$grafico_[$rs['imei']]['placa'] = $rs['placa'];
				$data_grafico1[] = $rs['data'];
				$speed_g[$rs['placa']][$rs['data']][] = $rs['speed'];
				// $imeis1[] = $rs['imei'];
			}

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
					
<td><a href="http://maps.google.com/maps?q=' . $latitudeDecimalDegrees . ',' .  $longitudeDecimalDegrees . '" target="_blank" class="external" ><img src="https://itajobi.usinaitajobi.com.br/imagens/mapa_globo.png"  title="Veiculo Ligado"> </a></td>

				</tr>';

		?>
			<script>
				posicoes.push({
					'data': '<?php echo $rs[data]; ?>',
					'data_comunica': '<?php echo $rs[data_comunica]; ?>',
					'minutocomunica': '<?php echo $rs[minutocomunica]; ?>',
					'status': '',
					'ignicao': '<?php echo $escreveSN; ?>',
					'evento': '',
					'voltagem_bateria': '<?php echo $rs[voltagem_bateria]; ?>',
					'address': '<?php echo $address; ?>',

					'placa': '<?php echo $rs[placa]; ?>',
					'velocidade': '<?php echo $rs[speed]; ?>',
					'rpm': '<?php echo $rs[rpm]; ?>',
					'km_rodado': '<?php echo $rs[km_rodado]; ?>',
					'cliente': '<?php echo $rs[cliente]; ?>',
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

	<!-- grafico -->
	<div class="modal fade" id="grafico" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" style="z-index: 1400;" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="exampleModalLongTitle">Gráfico</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="fechar()">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<canvas id='myChart' style='display: block; width: 500px; height: 1300px;' width='500' height='1300'></canvas>
					<?php
					function random_color()
					{
						$letters = '0123456789ABCDEF';
						$color = '#';
						for ($i = 0; $i < 6; $i++) {
							$index = rand(0, 15);
							$color .= $letters[$index];
						}
						return $color;
					}

					$dados_grafico = '';

					$dados_grafico = "data:{";
					$dados_grafico .= "labels:[";
					foreach ($data_grafico1 as $k => $v) {
						$dados_grafico .= "'" . $v . "',";
					}
					// for ($i = 0; $i < 10; $i++) {
					// 	$dados_grafico .= ",'" . $v . "'";
					// }
					$dados_grafico .= "],";

					$dados_grafico .= "datasets:[";

					$y = 0;
					foreach ($grafico_ as $key => $value) {
						if ($value['placa'] != '' && $value['placa'] != null) {
							if ($y == 0) {
								$dados_grafico .= ' { ';
							} else {
								$dados_grafico .= ' , { ';
							}

							$dados_grafico .= "
										label: '" . $value['placa'] . "',
										data:[";

							$placaaaa = $value['placa'];
							foreach ($data_grafico1 as $k => $v) {
								// = $rs['speed'];
								if ($speed_g[$placaaaa][$v] == null or $speed_g[$placaaaa][$v] == '') {
									$dados_grafico .= ",";
								} else {
									foreach ($speed_g[$placaaaa][$v] as $key => $value) {
										$dados_grafico .= $value . ",";
									}
								}
							}
							$dados_grafico .= "],
										borderWidth: 2,
										borderColor: '" . random_color() . "',
										backgroundColor: 'transparent',

										}";
						}


						$y = 1;
					}

					$dados_grafico .= "]}";

					?>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
				</div>
			</div>
		</div>
	</div>
	<!-- fim grafico -->

</div>

<script>
	function tracarMapa() {




		$("#mapa").show();
		$("#tabelaMapa").hide();

		var latlng = new google.maps.LatLng(-10.947765, -37.072953); //DEFINE A LOCALIZAÇÃO EXATA DO MAPA

		var myOptions = {
			zoom: 15,
			center: latlng,
			mapTypeId: google.maps.MapTypeId.ROADMAP
		};

		//CRIANDO O MAPA
		var_map = new google.maps.Map(document.getElementById("mapa"), myOptions);

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
				var iconeImg = 'https://itajobi.usinaitajobi.com.br/imagens/marker_end.png';
			} else if (i == (arr.length - 1)) {
				var iconeImg = 'https://itajobi.usinaitajobi.com.br/imagens/marker_start.png';
			} else {
				if (arr[i].ignicao == 'Sim') {
					var iconeImg = 'https://itajobi.usinaitajobi.com.br/imagens/ignicaoOn.png';
				} else {
					var iconeImg = 'https://itajobi.usinaitajobi.com.br/imagens/ignicaoOff.gif';
				}


			}

			path = line.getPath().getArray();
			latLng = new google.maps.LatLng(arr[i].lat, arr[i].lng);
			path.push(latLng);
			line.setPath(path);



			var_map.setCenter(latLng);

			var marker = new google.maps.Marker({
				map: var_map,
				position: latLng,
				icon: iconeImg

			});





			i--;
		};





	}
</script>

<script>
	function grafico() {




		var ctx = document.getElementById('myChart').getContext('2d');
		myLineChart = new Chart(ctx, {
			type: 'line',
			// data: {
			// 	labels: ['03/05/2021 00:11:13', '03/05/2021 10:19:29', '03/05/2021 10:19:29', '03/05/2021 00:11:14'],
			// 	datasets: [{
			// 		label: '8347-F3',
			// 		data: [0, 7, 7, 7],
			// 		borderWidth: 3,
			// 		borderColor: '#4DFE31',
			// 		backgroundColor: 'transparent',
			// 	}]
			// }
			<?php echo $dados_grafico ?>,
			options: {
				title: {
					display: true,
					fontSize: 20,
					text: 'Relatório de Excesso de Velocidade'
				},
				scales: {
					yAxes: [{
						ticks: {
							callback: function(value) {
								return value + ' Km/h';
							}
						}
					}]
				},
				tooltips: {
					callbacks: {
						title: function(tooltipItem, data) {
							return 'Data : ' + data['labels'][tooltipItem[0]['index']];
						},
						label: function(tooltipItem, data) {
							return 'Velocidade: ' + data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index] + ' Km/h';
						},
					},
					backgroundColor: '#FFF',
					titleFontSize: 14,
					titleFontColor: '#0066ff',
					bodyFontColor: '#000',
					bodyFontSize: 14,
					displayColors: false,
				}
			}
		});
	}
</script>

<!-- 
<script> 
        var ctx = document.getElementById('myChart').getContext('2d');
      var myChart = new Chart(ctx, {
          type: 'line',
          data: {
            labels: ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"],
            datasets: [{ 
                data: [86,114,106,106,107,111,133],
                label: "Total",
                borderColor: "#3e95cd",
                backgroundColor: "#7bb6dd",
                fill: false,
              }, { 
                data: [70,90,44,60,83,90,100],
                label: "Accepted",
                borderColor: "#3cba9f",
                backgroundColor: "#71d1bd",
                fill: false,
              }, { 
                data: [10,21,60,44,17,21,17],
                label: "Pending",
                borderColor: "#ffa500",
                backgroundColor:"#ffc04d",
                fill: false,
              }, { 
                data: [6,3,2,2,7,0,16],
                label: "Rejected",
                borderColor: "#c45850",
                backgroundColor:"#d78f89",
                fill: false,
              }
            ]
          },
        });
    </script> 
-->