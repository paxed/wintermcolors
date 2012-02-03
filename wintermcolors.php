<?php

if (isset($_GET['r']) && preg_match('/^[01]$/', $_GET['r'])) {
  $def_regtype = $_GET['r'];
} else {
  $def_regtype = 0;
}

if (isset($_GET['c']) && preg_match('/^[0-9]+$/', $_GET['c'])) {
  $def_colorset = $_GET['c'];
} else {
  $def_colorset = 0;
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {

  $str = "REGEDIT4\r\n\r\n";

  switch ($_POST['regtype']) {
  default:
  case 0:
    $str .= "[HKEY_CURRENT_USER\Console]\r\n";
    for ($x = 0; $x < 16; $x++) {
      $str .= sprintf("\"ColorTable%02d\"=dword:00%06s", $x, $_POST['col'.$x])."\r\n";
    }
    $str .= "\r\n";

    $filename = "win_console_colors.reg";
    break;
  case 1:
    /* putty has colors in weird order, plus they don't start at 0 */
    $putty_reorder = array(6,8,10,12,14,16,18,20, 7,9,11,13,15,17,19,21);
    $str .= "[HKEY_CURRENT_USER\Software\SimonTatham\PuTTY\Sessions\Default%20Settings]\r\n";
    $str .= "\"BoldAsColour\"=dword:00000001\r\n";
    for ($x = 0; $x < 16; $x++) {
      $r = hexdec(substr($_POST['col'.$x], 0, 2));
      $g = hexdec(substr($_POST['col'.$x], 2, 2));
      $b = hexdec(substr($_POST['col'.$x], 4, 2));
      $str .= sprintf("\"Colour%d\"=\"%d,%d,%d\"", $putty_reorder[$x], $r,$g,$b)."\r\n";
    }
    $str .= "\r\n";

    $filename = "putty_colors.reg";
    break;
  case 2:
      $str = "#!/bin/sh\n";
      for ($x = 0; $x < 16; $x++) {
	  $str .= sprintf("echo -e '\\e]P%x%06s'", $x, strtolower($_POST['col'.$x]))."\n";
      }
      $filename = "linux_terminal_colors.sh";
      break;
  case 3:
      $str = "! Save this file as ~/.Xdefaults and use \"xrdb -merge ~/.Xdefaults\" to active the changes.\n";
      for ($x = 0; $x < 16; $x++) {
	  $str .= sprintf("XTerm*color%d: #%06s", $x, strtolower($_POST['col'.$x]))."\n";
      }
      $filename = "xterm_xdefaults.txt";
      break;
  case 4:
      $str = "! Save this file as ~/.Xdefaults and use \"xrdb -merge ~/.Xdefaults\" to active the changes.\n";
      for ($x = 0; $x < 16; $x++) {
	  $str .= sprintf("Rxvt*color%d: #%06s", $x, strtolower($_POST['col'.$x]))."\n";
      }
      $filename = "rxvt_xdefaults.txt";
      break;

  }

  header('Content-Type: binary/octet-stream');
  header('Content-Length: '.strlen($str));
  header('Content-Disposition: attachment; filename="'.$filename.'"');

  print $str;

  exit;

}


?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Console colors editor</title>
<script type="text/javascript">
<!--
var colortable = new Array();

var default_regtype = <?php echo $def_regtype; ?>;
var default_colorset = <?php echo $def_colorset; ?>;

var regtypes = new Array(
 {'name':"Windows cmdline, current user (registry file)", 'regtype':0},
 {'name':"PuTTY (registry file)", 'regtype':1},
 {'name':"Linux terminal (sh script)", 'regtype':2},
 {'name':"xterm (X11 resource file)", 'regtype':3},
 {'name':"rxvt (X11 resource file)", 'regtype':4}
);

var colors = new Array(
{'name':"Default Windows console colors",'pal':new Array(
							   "000000",
							   "800000",
							   "008000",
							   "808000",
							   "000080",
							   "800080",
							   "008080",
							   "C0C0C0",
							   "808080",
							   "FF0000",
							   "00FF00",
							   "FFFF00",
							   "0000FF",
							   "FF00FF",
							   "00FFFF",
							   "FFFFFF")},
    {'name':"RXVT", 'pal':new Array(
				  "000000",
				  "cd0000",
				  "00cd00",
				  "cdcd00",
				  "0000cd",
				  "cd00cd",
				  "00cdcd",
				  "faebd7",
				  "404040",
				  "ff0000",
				  "00ff00",
				  "ffff00",
				  "0000ff",
				  "ff00ff",
				  "00ffff",
				  "ffffff")},
    {'name':"XTerm", 'pal':new Array(
				       "000000",
				       "cd0000",
				       "00cd00",
				       "cdcd00",
				       "1e90ff",
				       "cd00cd",
				       "00cdcd",
				       "e5e5e5",
				       "4c4c4c",
				       "ff0000",
				       "00ff00",
				       "ffff00",
				       "4682b4",
				       "ff00ff",
				       "00ffff",
				       "ffffff")},
    {'name':"Linux console", 'pal':new Array(
					       "000000",
					       "aa0000",
					       "00aa00",
					       "aa5500",
					       "0000aa",
					       "aa00aa",
					       "00aaaa",
					       "aaaaaa",
					       "555555",
					       "ff5555",
					       "55ff55",
					       "ffff55",
					       "5555ff",
					       "ff55ff",
					       "55ffff",
					       "ffffff")},
    {'name':"Gnome-terminal \"Tango\" theme", 'pal':new Array(
								"2e3436",
								"cc0000",
								"4e9a06",
								"c4a000",
								"3465a4",
								"75507b",
								"06989a",
								"d3d7cf",
								"555753",
								"ef2929",
								"8ae234",
								"fce94f",
								"729fcf",
								"ad7fa8",
								"34e2e2",
								"eeeeec")},

    {'name':"konsole \"Dark Pastels\" theme", 'pal':new Array(
								"3F3F3F",
								"705050",
								"60B48A",
								"DFAF8F",
								"9AB9D7",
								"DC8CC4",
								"8CD1D3",
								"DCDCCD",
								"709080",
								"DCA3A3",
								"72D5A2",
								"F0DFAF",
								"94C0F3",
								"EC93D5",
								"93E1E3",
								"FFFFFF")},


    {'name':"Equiluminous", /* Ilmari Karonen */
     'desc':"All dark colors have 50% lightness, all light colors have 75% lightness (assuming gamma=2.2).",
     'pal':new Array("3F3F3F",
		     "DC0000",
		     "00A200",
		     "A17500",
		     "6161FF",
		     "BF00BF",
		     "009595",
		     "BFBFBF",
		     "7F7F7F",
		     "FF9A9A",
		     "00F300",
		     "C9C900",
		     "B5B5FF",
		     "FF7CFF",
		     "00E0E0",
		     "FFFFFF")},

    {'name':"Bitcount", /* Ilmari Karonen */
     'desc':"Lightness is proportional to sum of bits in color code plus one (assuming gamma=2.2; brown counts as 1.5 bits).",
     'pal':new Array("333333",
		     "B00000",
		     "008100",
		     "A17500",
		     "3030FF",
		     "E500E5",
		     "00B3B3",
		     "CCCCCC",
		     "656565",
		     "FF3838",
		     "00C200",
		     "D7D700",
		     "8585FF",
		     "FF9CFF",
		     "00EFEF",
		     "FFFFFF")}

);


function init_colortable(num)
{
  var x;
  for (x = 0; x < 16; x++) {
    colortable[x] = colors[num].pal[x];
  }
}

function get_regtype_input()
{
  var ret = "";
  ret += "<select name='regtype'>";

  for (i = 0; i < regtypes.length; i++) {
    ret += "<option value='"+regtypes[i].regtype+"'";
    if (i == default_regtype) ret += " selected";
    ret += ">"+regtypes[i].name+"</option>";
  }

  ret += "</select>";
  return ret;
}

function get_color_input(colnum, col)
{
  var ret = "";
  ret += "<input style='font-family:monospace;'";
  ret += " onchange='recalc_color("+colnum.toString()+");'";
  ret += " onblur='recalc_color("+colnum.toString()+");'";
  ret += " type='text' id='col" + colnum.toString() + "'";
  ret += " name='col" + colnum.toString() + "'";
  ret += " size='6' maxlength='6' value='" + col + "'>";
  return ret;
}


function recalc_color(colnum)
{
  var tmp = document.getElementById("colexample"+colnum.toString());
  var val = document.getElementById("col"+colnum.toString());

  if (!tmp) return;
  if (!val) return;

  if (val.value.match('[^0-9a-fA-F]')) {
    val.value = colortable[colnum];
  }

  if (val.value.length < 1) {
    val.value = colortable[colnum];
  } else if (val.value.length == 3) {
    val.value = val.value[0] + "0" + val.value[1] + "0" + val.value[2] + "0";
  } else {
    while (val.value.length < 6) {
      val.value += '0';
    }
  }

  tmp.style.background = "#" + val.value;

  write_sample();
}

function draw_colorinput()
{
  var tmp = document.getElementById("editdiv");
  var txt = '';
  var x;

  if (!tmp) return;

  for (x = 0; x < 16; x++) {
    txt += "<span>";
    txt += get_color_input(x, colortable[x]);
    txt += "&nbsp;";
    txt += "<a href='#' onclick='write_colorpicker("+x+"); return false;' style='text-decoration:none;font-family:monospace;border:.25em solid black;background:#"+colortable[x]+";' id='colexample"+x+"'>";
    txt += "&nbsp;&nbsp;";
    txt += "<\/a>";
    txt += "<\/span><br>";
  }

  txt += "<p><input type='Submit' value='Download'>";

  tmp.innerHTML = txt;
}

var previous_pre = -1;

function set_all_colors(num)
{
  if ((num < 0) || (num >= colors.length)) { num = 0; }

  var tmp = document.getElementById("pre"+num);

  init_colortable(num);
  draw_colorinput();

  if (tmp) {
    if (previous_pre != -1) {
      var prev_tmp = document.getElementById("pre"+previous_pre);
      prev_tmp.style.background='none';
    }
    tmp.style.background='lightgrey';
    previous_pre = num;
    colorpicker_close();
    write_sample();
  }
}

function rgb2hex(r,g,b)
{
  var txt = "";
  var tmp;
  tmp = r.toString(16);
  while (tmp.length < 2) tmp = "0" + tmp;
  txt += tmp;

  tmp = g.toString(16);
  while (tmp.length < 2) tmp = "0" + tmp;
  txt += tmp;

  tmp = b.toString(16);
  while (tmp.length < 2) tmp = "0" + tmp;
  txt += tmp;

  return txt;
}

function set_picked_color(coloridx, r,g,b)
{
  var tmp = document.getElementById("colorpickerspan");
  var val = document.getElementById("col"+coloridx.toString());
  if (!tmp) return;
  if (!val) return;

  tmp.innerHTML = "";

  val.value = rgb2hex(r,g,b);

  tmp = document.getElementById("colexample"+coloridx.toString());
  if (!tmp) return;
  tmp.style.background = "#" + val.value;
  write_sample();
}

function set_picker_example(r,g,b)
{
  var tmp = document.getElementById("colorpicker_example");
  if (!tmp) return;
  tmp.innerHTML = rgb2hex(r,g,b);
  tmp.style.background = "#" + rgb2hex(r,g,b);
  if ((r < 120) && (g < 120) && (b < 120)) {
    tmp.style.color = "white";
  } else {
    tmp.style.color = "black";
  }
}

function colorpicker_close()
{
  var tmp = document.getElementById("colorpickerspan");
  if (tmp == undefined) return;
  tmp.innerHTML = "";
}

function write_colorpicker(coloridx)
{
  var tmp = document.getElementById("colorpickerspan");
  var origcolor = document.getElementById("col"+coloridx.toString());

  if (tmp == undefined) return;

  var txt = "";
  var i,r,g,b;

  orig_r = parseInt(origcolor.value[0],16)*16 + parseInt(origcolor.value[1], 16);
  orig_g = parseInt(origcolor.value[2],16)*16 + parseInt(origcolor.value[3], 16);
  orig_b = parseInt(origcolor.value[4],16)*16 + parseInt(origcolor.value[5], 16);

  txt += "<p><span style='font-family:monospace' id='colorpicker_example'>000000</span>&nbsp;-&nbsp;";
  txt += "<span style='font-family:monospace;background-color:#"+origcolor.value+"'>"+origcolor.value+"</span>";

  txt += "<br>";

  for (i = 0; i < 256; i += 8) {
    r = Math.floor(orig_r*(i/256));
    g = Math.floor(orig_g*(i/256));
    b = Math.floor(orig_b*(i/256));
    txt += "<a href='#' onMouseOver='set_picker_example("+r+","+g+","+b+");' onclick='set_picked_color("+coloridx+","+r+","+g+","+b+");' style='background-color:rgb("+r+","+g+","+b+");text-decoration:none;'>&nbsp;</a>";
  }

  txt += "<br><br>";

  for (i = 0; i < 256; i += 8) {
    r = i;
    g = orig_g;
    b = orig_b;
    txt += "<a href='#' onMouseOver='set_picker_example("+r+","+g+","+b+");' onclick='set_picked_color("+coloridx+","+r+","+g+","+b+");' style='background-color:rgb("+r+","+g+","+b+");text-decoration:none;'>&nbsp;</a>";
  }

  txt += "<br>";
  for (i = 0; i < 256; i += 8) {
    r = orig_r;
    g = i;
    b = orig_b;
    txt += "<a href='#' onMouseOver='set_picker_example("+r+","+g+","+b+");' onclick='set_picked_color("+coloridx+","+r+","+g+","+b+");' style='background-color:rgb("+r+","+g+","+b+");text-decoration:none;'>&nbsp;</a>";
  }
  txt += "<br>";
  for (i = 0; i < 256; i += 8) {
    r = orig_r;
    g = orig_g;
    b = i;
    txt += "<a href='#' onMouseOver='set_picker_example("+r+","+g+","+b+");' onclick='set_picked_color("+coloridx+","+r+","+g+","+b+");' style='background-color:rgb("+r+","+g+","+b+");text-decoration:none;'>&nbsp;</a>";
  }

  tmp.innerHTML = txt;
}

function write_sample()
{
  var tmp = document.getElementById("samplediv");
  var txt = 'Sample:<br><span style="font-family:monospace;background-color:black;">';
  var i, r;

  txt += "&nbsp;";

  for (i = 0; i < 16; i++) { txt += "&nbsp;&nbsp;&nbsp;"; }
  txt += "&nbsp;<br>&nbsp;";
  for (i = 0; i < 16; i++) {
    var chr = '@'; //String.fromCharCode(Math.floor((Math.random() * ('~'.charCodeAt(0) - ' '.charCodeAt(0)+1)) + ' '.charCodeAt(0)));
    var origcolor = document.getElementById("col"+i.toString());
    txt += "&nbsp;<span style='color:#"+origcolor.value+"'>" + chr + "</span>&nbsp;";
  }
  txt += "&nbsp;<br>&nbsp;";
  for (i = 0; i < 16; i++) { txt += "&nbsp;&nbsp;&nbsp;"; }
  txt += "&nbsp;</span>";
  tmp.innerHTML = txt;
}

function setcolordiv_content()
{
  var tmp = document.getElementById("presetsdiv");
  var txt = '';
  var i;

  if (!tmp) return;

  txt += "Set all colors to preset values:<ul>";

  for (i = 0; i < colors.length; i++) {
    txt += "<li><a href='javascript:set_all_colors("+i+")' id='pre"+i+"'>"+colors[i].name+"<\/a>";
    if (typeof(colors[i].desc) == "string") {
      txt += ": "+colors[i].desc;
    }
  }
  txt += "<\/ul>";

  txt += "Create a file for:";
  txt += get_regtype_input();

  tmp.innerHTML = txt;
}
//-->
</script>
</head>
<body>

<h1>Console colors editor</h1>

<div>
If you are having problems because the blue is too dark,
or the brown is too gray, or you hate the orange(bright-red),
then you probably want to tweak the color settings for your text console.
<p>
Edit the colors below (or click on a predefined settings)
and press the Download-button.
</div>

<form method='post' action='<?php echo $_SERVER['PHP_SELF'] ?>'>

<div style="float:left;padding:1em;margin-right:2em" id="editdiv"></div>
<div style="padding:1em" id="presetsdiv"></div>
<div id="samplediv"></div>
<div style="font-family:monospace;" id="colorpickerspan"></div>

</form>

<script type="text/javascript">
<!--
setcolordiv_content();
set_all_colors(default_colorset);
write_sample();
//-->
</script>
<noscript><p><b>Sorry, this page requires javascript.</b></p></noscript>

</body>
</html>
