export default function createHtmlReportPosition(
  positions,
  base64PrintScreenMap,
) {
  let tableContent = '';

  positions.forEach((p) => {
    tableContent += `
               <tr>
                 <td>${p.placa}</td>
                 <td>${p.data}</td>
                 <td>${p.data_comunica}</td>
                 <td class="table-address">${p.address}</td>
                 <td>${p.velocidade} KM/h</td>
                 <td>${p.ignicao}</td>
                 <td>${p.km_rodado}</td>
               </tr>
             `;
  });

  return `
  <body>
  <h2>Relatório de Posições</h2>
  <div class="table-wrapper">
      <table class="fl-table">
         <thead>
         <tr>
             <th>Placa</th>
             <th>Data</th>
             <th>Data Comunicação</th>
             <th>Endereço</th>
             <th>Velocidade</th>
             <th>Ligado</th>
             <th>KM</th>
         </tr>
         </thead>
         <tbody>
         ${tableContent}
         <tbody>
     </table>

     <img class="img-map-print"
     src='data:image/jpg;base64,${base64PrintScreenMap}' />
 </div>

<style type="text/css">
*{
box-sizing: border-box;
-webkit-box-sizing: border-box;
-moz-box-sizing: border-box;
}
body{
font-family: Helvetica;
-webkit-font-smoothing: antialiased;
}
h2{
text-align: center;
font-size: 18px;
text-transform: uppercase;
letter-spacing: 1px;
color: #333333;
padding: 30px 0;
}

/* Table Styles */

.table-wrapper{

}

.fl-table {
border-radius: 5px;
font-size: 12px;
font-weight: normal;
width: 100%;
max-width: 100%;
background-color: white;
}

.fl-table td, .fl-table th {
text-align: center;
padding: 8px;
}

.fl-table td {
border: 1px solid #dad6d6;
font-size: 12px;
}

.fl-table thead th {
border : 1px solid #dad6d6;
}

.fl-table td {
border-right: 1px solid #dad6d6;
font-size: 12px;
}

.fl-table tr:nth-child(even) {
background: #F8F8F8;
}

.table-address {
    max-width : 120px;
    word-wrap: break-word;
}

.img-map-print{
    width: 100%;
    margint-top : 24px;
}
</style>

</body>
`;
}
