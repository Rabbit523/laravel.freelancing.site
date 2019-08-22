<?php



$html = ' 
<h1>mPDF</h1>
<h2>Tables</h2>
<h3>CSS Styles</h3>
<p>The CSS properties for tables and cells is increased over that in html2fpdf. It includes recognition of THEAD, TFOOT and TH.<br />See below for other facilities such as autosizing, and rotation.</p>
<table border="1">
<tbody><tr><td>Row 1</td><td>This is data</td><td>This is data</td></tr>

<tr><td>Row 2</td>

<td style="background-gradient: linear #c7cdde #f0f2ff 0 1 0 0.5;">
<p>This is data p</p>
This is data out of p
<p style="font-weight:bold; font-size:20pt; background-color:#FFBBFF;">This is bold data p</p>
<b>This is bold data out of p</b><br />
This is normal data after br
<h3>H3 in a table</h3>
<div>This is data div</div>
This is data out of div
<div style="font-weight:bold;">This is data div (bold)</div>
This is data out of div
</td>


<td><p>More data</p><p style="font-size:12pt;">This is large text</p></td></tr>
<tr><td><p>Row 3</p></td><td><p>This is long data</p></td><td>This is data</td></tr>
<tr><td><p>Row 4 &lt;td&gt; cell</p></td><td>This is data</td><td><p>This is data</p></td></tr>
<tr><td>Row 5</td><td>Also data</td><td>Also data</td></tr>
<tr><td>Row 6</td><td>Also data</td><td>Also data</td></tr>
<tr><td>Row 7</td><td>Also data</td><td>Also data</td></tr>
<tr><td>Row 8</td><td>Also data</td><td>Also data</td></tr>
</tbody></table>

';

//==============================================================
//==============================================================
//==============================================================
include("mpdf.php");

$mpdf=new mPDF('c','A4','','',32,25,27,25,16,13); 

$mpdf->SetDisplayMode('fullpage');

$mpdf->list_indent_first_level = 0;	// 1 or 0 - whether to indent the first level of a list

// LOAD a stylesheet
$stylesheet = file_get_contents('mpdfstyletables.css');
$mpdf->WriteHTML($stylesheet,1);	// The parameter 1 tells that this is css/style only and no body/html/text

$mpdf->WriteHTML($html,2);

$mpdf->Output('mpdf.pdf','I');
exit;
//==============================================================
//==============================================================
//==============================================================


?>