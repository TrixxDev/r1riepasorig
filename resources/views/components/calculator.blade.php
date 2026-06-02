<head>
  <title>Riepu izmēra kalkulators</title>
  <link rel="icon" type="image/png" href="{{ asset('images/logo_big.png') }}">
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <link rel="stylesheet" href="template/common/calc.css" type="text/css" media="screen">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" integrity="sha512-MV7K8+y+gLIBoVD59lQIYicR65iaqukzvf/nwasF0nqhPay5w/9lJmVM2hMDcnK1OnMGCdVK+iQrJ7lzPJQd1w==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <style>
    td, input.btn, .hide{
      font-size: 2rem;
    }

    select {
      margin-right: 5px;
    }
    .table-sm td, .table-sm th {
      vertical-align: middle;
    }
  </style>
</head>
<body style="font-family: 'Arimo', Arial, sans-serif; margin: 0 10px;">
<script>



  if (localStorage.getItem('calc')) {
    if (!window.opener && !window.opener !== window) {
      window.onload = function () {
        document.getElementById('home_button').innerHTML = '<button onclick="getBackHome();" class="btn btn-danger mt-1" style="font-size: 2rem;"><i class="fa-solid fa-house"></i></button>';
      }
    }

    if(/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
      window.onload = function () {
        document.getElementById('home_button').innerHTML = '<button onclick="getBackHome();" class="btn btn-danger mt-1" style="font-size: 2rem;"><i class="fa-solid fa-house"></i></button>';
      }
    }
  } else { // IF NOT OPENED FROM WEBSITE
    if (!window.opener && !window.opener !== window) {
      window.onload = function () {
        document.getElementById('home_button').innerHTML = '<a href="/" class="btn btn-danger mt-1" style="font-size: 2rem;"><i class="fa-solid fa-house"></i></a>';
      }
    }

    if(/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
      window.onload = function () {
        document.getElementById('home_button').innerHTML = '<a href="/" class="btn btn-danger mt-1" style="font-size: 2rem;"><i class="fa-solid fa-house"></i></a>';
      }
    }
  }

  function getBackHome() {
    localStorage.removeItem('calc');
    window.history.back();
  }

  function insert(textEl,text){
    textEl.value = '';
    textEl.value += text;
    // textEl.focus();
  }

  function round_decimals(orginal_number, decimals){
    var result1= orginal_number*Math.pow(10,decimals)
    var result2=Math.round(result1)
    var result3=result2/Math.pow(10, decimals)
    return result3
  }

  function insert2(code){
    var platums=document.kalkulators.org_platums.options[document.kalkulators.org_platums.selectedIndex].value;
    var augstums=document.kalkulators.org_augstums.options[document.kalkulators.org_augstums.selectedIndex].value;
    var disks=document.kalkulators.org_disks.options[document.kalkulators.org_disks.selectedIndex].value;
    var spidometrs=document.kalkulators.org_spidometrs.options[document.kalkulators.org_spidometrs.selectedIndex].value;
    var diametrsx1=((platums*(augstums/100))*2)+(disks*25.4);
    var diametrsx2=round_decimals(diametrsx1,10);
    var radiussx1=diametrsx2/2;

    var platumsa1=document.kalkulators.platums1.options[document.kalkulators.platums1.selectedIndex].value;
    var augstumsa1=document.kalkulators.augstums1.options[document.kalkulators.augstums1.selectedIndex].value;
    var disksa1=document.kalkulators.disks1.options[document.kalkulators.disks1.selectedIndex].value;
    var diametrsa1=((platumsa1*(augstumsa1/100))*2)+(disksa1*25.4);
    var diametrsb1=round_decimals(diametrsa1,10);
    var radiussa1=diametrsb1/2;

    var platumsa2=document.kalkulators.platums2.options[document.kalkulators.platums2.selectedIndex].value;
    var augstumsa2=document.kalkulators.augstums2.options[document.kalkulators.augstums2.selectedIndex].value;
    var disksa2=document.kalkulators.disks2.options[document.kalkulators.disks2.selectedIndex].value;
    var diametrsa2=((platumsa2*(augstumsa2/100))*2)+(disksa2*25.4);
    var diametrsb2=round_decimals(diametrsa2,10);
    var radiussa2=diametrsb2/2;

    var platumsa3=document.kalkulators.platums3.options[document.kalkulators.platums3.selectedIndex].value;
    var augstumsa3=document.kalkulators.augstums3.options[document.kalkulators.augstums3.selectedIndex].value;
    var disksa3=document.kalkulators.disks3.options[document.kalkulators.disks3.selectedIndex].value;
    var diametrsa3=((platumsa3*(augstumsa3/100))*2)+(disksa3*25.4);
    var diametrsb3=round_decimals(diametrsa3,10);
    var radiussa3=diametrsb3/2;

    insert(document.kalkulators.org_radiuss, radiussx1);
    var radiussc1=radiussa1-radiussx1;
    radiussc1=round_decimals(radiussc1,2);
    insert(document.kalkulators.radiuss1, radiussc1);
    var radiussc2=radiussa2-radiussx1;
    radiussc2=round_decimals(radiussc2,2);
    insert(document.kalkulators.radiuss2, radiussc2);
    var radiussc3=radiussa3-radiussx1;
    radiussc3=round_decimals(radiussc3,2);
    insert(document.kalkulators.radiuss3, radiussc3);

    var spidometrsa1=spidometrs*((radiussx1+radiussc1)/radiussx1)
    spidometrsa1=round_decimals(spidometrsa1,1);
    insert(document.kalkulators.spidometrs1, spidometrsa1);

    var spidometrsa2=spidometrs*((radiussx1+radiussc2)/radiussx1)
    spidometrsa2=round_decimals(spidometrsa2,1);
    insert(document.kalkulators.spidometrs2, spidometrsa2);

    var spidometrsa3=spidometrs*((radiussx1+radiussc3)/radiussx1)
    spidometrsa3=round_decimals(spidometrsa3,1);
    insert(document.kalkulators.spidometrs3, spidometrsa3);
  }

</script>

{{--<a href="/" class="btn btn-danger mt-1" style="font-size: 2rem;">Atgriezties uz lapu <i class="fa-solid fa-rotate-left"></i></a>--}}
<div id="home_button"></div>


  <table border="0" style="width: 100%;">
    <tr>
      <td>
        <form method="POST" action="#" name="kalkulators">
          <div align="center">
            <center>
              <table border="1" cellpadding="2" cellspacing="0" class="table table-sm" style="margin-top: 10px;">
                <tr>
                  <td></td>
                  <td><B class="small">platums/augstums/disks =Radiuss mm </b>
                  </td>
                  <td><B class="small">km/h</b></td>
                </tr>
                <tr>
                  <td><B class="small">Oriģinālais</b></td>

                  <td><select size="1" name="org_platums">
                      <option  value="145">145</option>
                      <option  value="155">155</option>
                      <option  value="165">165</option>
                      <option  value="175">175</option>
                      <option  value="185">185</option>
                      <option  value="195">195</option>
                      <option selected="selected" value="205">205</option>
                      <option  value="215">215</option>
                      <option  value="225">225</option>
                      <option  value="235">235</option>
                      <option  value="245">245</option>
                      <option  value="255">255</option>
                      <option  value="265">265</option>
                      <option  value="275">275</option>
                      <option  value="285">285</option>
                      <option  value="295">295</option>
                      <option  value="305">305</option>
                      <option  value="315">315</option>
                      <option  value="325">325</option>
                    </select><B class="small"> /</b>

                    <select size="1" name="org_augstums">
                      <option  value="25">25</option>
                      <option  value="30">30</option>
                      <option  value="35">35</option>
                      <option  value="40">40</option>
                      <option  value="45">45</option>
                      <option  value="50">50</option>
                      <option selected value="55">55</option>
                      <option  value="60">60</option>
                      <option  value="65">65</option>
                      <option  value="70">70</option>
                      <option  value="75">75</option>
                      <option  value="80">80</option>
                    </select><B class="small"> R</b>

                    <select size="1" name="org_disks">
                      <option  value="13">13</option>
                      <option  value="14">14</option>
                      <option  value="15">15</option>
                      <option selected value="16">16</option>
                      <option  value="17">17</option>
                      <option  value="18">18</option>
                      <option  value="19">19</option>
                      <option  value="20">20</option>
                      <option  value="21">21</option>
                      <option  value="22">22</option>
                      <option  value="23">23</option>
                      <option  value="24">24</option>
                    </select>
                    <input type="text" name="org_radiuss" readOnly size="6" style="width: 30%;"></td>
                  <td>
                    <select size="1" name="org_spidometrs" style="width: 100%;">
                      <option  value="40">40</option><option  value="50">50</option><option  value="60">60</option><option  value="70">70</option><option  value="80">80</option><option selected value="90">90</option><option  value="100">100</option><option  value="110">110</option><option  value="120">120</option><option  value="130">130</option><option  value="140">140</option><option  value="150">150</option><option  value="160">160</option><option  value="170">170</option><option  value="180">180</option><option  value="190">190</option><option  value="200">200</option><option  value="210">210</option><option  value="220">220</option><option  value="230">230</option><option  value="240">240</option><option  value="250">250</option><option  value="260">260</option>
                    </select>
                    <!--
                    <input type="text" name="org_spidometrs" size="11" value="100">
                    -->
                  </td>
                </tr>
                <tr>
                  <td><B class="small">Variants 1</b></td>
                  <td><select size="1" name="platums1">
                      <option  value="145">145</option>
                      <option  value="155">155</option>
                      <option  value="165">165</option>
                      <option  value="175">175</option>
                      <option  value="185">185</option>
                      <option selected="selected" value="195">195</option>
                      <option value="205">205</option>
                      <option  value="215">215</option>
                      <option  value="225">225</option>
                      <option  value="235">235</option>
                      <option  value="245">245</option>
                      <option  value="255">255</option>
                      <option  value="265">265</option>
                      <option  value="275">275</option>
                      <option  value="285">285</option>
                      <option  value="295">295</option>
                      <option  value="305">305</option>
                      <option  value="315">315</option>
                      <option  value="325">325</option>
                    </select><B class="small"> /</b>
                    <select size="1" name="augstums1">
                      <option  value="25">25</option>
                      <option  value="30">30</option>
                      <option  value="35">35</option>
                      <option  value="40">40</option>
                      <option  value="45">45</option>
                      <option  value="50">50</option>
                      <option  value="55">55</option>
                      <option  value="60">60</option>
                      <option selected value="65">65</option>
                      <option  value="70">70</option>
                      <option  value="75">75</option>
                      <option  value="80">80</option>
                    </select><B class="small"> R</b>
                    <select size="1" name="disks1">
                      <option  value="13">13</option>
                      <option  value="14">14</option>
                      <option selected value="15">15</option>
                      <option  value="16">16</option>
                      <option  value="17">17</option>
                      <option  value="18">18</option>
                      <option  value="19">19</option>
                      <option  value="20">20</option>
                      <option  value="21">21</option>
                      <option  value="22">22</option>
                      <option  value="23">23</option>
                      <option  value="24">24</option>
                    </select>
                    <input type="text" name="radiuss1" readOnly size="6" style="width: 30%;">&nbsp;<B class="small"><sup>*</sup></b></td>
                  <td><input type="text" name="spidometrs1" readOnly size="5" style="width: 100%;"></td>
                </tr>
                <tr>
                  <td><B class="small">Variants 2</b></td>
                  <td><select size="1" name="platums2">
                      <option  value="145">145</option>
                      <option  value="155">155</option>
                      <option  value="165">165</option>
                      <option  value="175">175</option>
                      <option  value="185">185</option>
                      <option  value="195">195</option>
                      <option  value="205">205</option>
                      <option  value="215">215</option>
                      <option selected="selected" value="225">225</option>
                      <option  value="235">235</option>
                      <option  value="245">245</option>
                      <option  value="255">255</option>
                      <option  value="265">265</option>
                      <option  value="275">275</option>
                      <option  value="285">285</option>
                      <option  value="295">295</option>
                      <option  value="305">305</option>
                      <option  value="315">315</option>
                      <option  value="325">325</option>
                    </select><B class="small"> /</b>
                    <select size="1" name="augstums2">
                      <option  value="25">25</option>
                      <option  value="30">30</option>
                      <option  value="35">35</option>
                      <option  value="40">40</option>
                      <option  selected value="45">45</option>
                      <option  value="50">50</option>
                      <option  value="55">55</option>
                      <option  value="60">60</option>
                      <option  value="65">65</option>
                      <option  value="70">70</option>
                      <option  value="75">75</option>
                      <option  value="80">80</option>
                    </select><B class="small"> R</b>
                    <select size="1" name="disks2">
                      <option  value="13">13</option>
                      <option  value="14">14</option>
                      <option  value="15">15</option>
                      <option  value="16">16</option>
                      <option selected value="17">17</option>
                      <option  value="18">18</option>
                      <option  value="19">19</option>
                      <option  value="20">20</option>
                      <option  value="21">21</option>
                      <option  value="22">22</option>
                      <option  value="23">23</option>
                      <option  value="24">24</option>
                    </select>
                    <input type="text" name="radiuss2" readOnly size="6" style="width: 30%;">&nbsp;<B class="small"><sup>*</sup></b></td>
                  <td><input type="text" name="spidometrs2" readOnly size="5" style="width: 100%;"></td>
                </tr>
                <tr>
                  <td><B class="small">Variants 3</b></td>
                  <td>
                    <select size="1" name="platums3">
                      <option  value="145">145</option>
                      <option  value="155">155</option>
                      <option  value="165">165</option>
                      <option  value="175">175</option>
                      <option  value="185">185</option>
                      <option  value="195">195</option>
                      <option  value="205">205</option>
                      <option  value="215">215</option>
                      <option selected value="225">225</option>
                      <option  value="235">235</option>
                      <option  value="245">245</option>
                      <option  value="255">255</option>
                      <option  value="265">265</option>
                      <option  value="275">275</option>
                      <option  value="285">285</option>
                      <option  value="295">295</option>
                      <option  value="305">305</option>
                      <option  value="315">315</option>
                      <option  value="325">325</option>
                    </select><B class="small"> /</b>
                    <select size="1" name="augstums3">
                      <option  value="25">25</option>
                      <option  value="30">30</option>
                      <option  value="35">35</option>
                      <option selected value="40">40</option>
                      <option  value="45">45</option>
                      <option  value="50">50</option>
                      <option  value="55">55</option>
                      <option  value="60">60</option>
                      <option  value="65">65</option>
                      <option  value="70">70</option>
                      <option  value="75">75</option>
                      <option  value="80">80</option>
                    </select><B class="small"> R</b>

                    <select size="1" name="disks3">
                      <option  value="13">13</option>
                      <option  value="14">14</option>
                      <option  value="15">15</option>
                      <option  value="16">16</option>
                      <option  value="17">17</option>
                      <option selected value="18">18</option>
                      <option  value="19">19</option>
                      <option  value="20">20</option>
                      <option  value="21">21</option>
                      <option  value="22">22</option>
                      <option  value="23">23</option>
                      <option  value="24">24</option>
                    </select>
                    <input type="text" name="radiuss3" readonly="readonly" size="6" style="width: 30%;">&nbsp;<B class="small"><sup>*</sup></b></td>
                  <td><input type="text" name="spidometrs3" readOnly size="5" style="width: 100%;"></td>

                </tr>
                <tr><td colspan="3"><B class="small">* starpība rādiusā mm</b></td><tr>
              </table>
            </center>
          </div>
          <input class="btn btn-primary" type="button" value="Aprēķināt" name="aprekinat"  onclick="javascript:insert2(':p')">
          <input class="btn btn-info float-right ml-1" type="button" value="&nbsp;&nbsp;&nbsp; Drukāt &nbsp;&nbsp;&nbsp;" name="notirit" onClick="print()">
          <input class="btn btn-secondary float-right" type="reset" value="&nbsp;&nbsp;&nbsp;Notīrīt&nbsp;&nbsp;&nbsp;" name="notirit">
        </form>
      </td>
    </tr>
  </table>

  <div class="hide"><b>Kalnciema riepu serviss:</b> Rīga, Kalnciema 39, tel. 67615615<br>
    <b>Ulbrokas riepu serviss:</b> Ulbroka, Acones iela 2A, tel. 67910555<br>
  </div>
</body>
