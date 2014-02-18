<?php
if (isset($_POST['m'])) {
	include('dns.inc.php');
	function query($domain,$v6 = FALSE){
		global $dns_query;
		if ($v6) $r = 'AAAA'; else $r = 'A';
		$result = $dns_query->Query($domain,$r);
		if ( ($result===false) || ($dns_query->error!=0) )	{
			if ($v6) return query($domain,false);
			return array("answer"=>($dns_query->error),"err"=>TRUE);
		} else {
			$result_count=$result->count; 
			for ($a=0; $a<$result_count; $a++) {
				if ($result->results[$a]->typeid==$r) {
					return array("answer"=>$result->results[$a]->data,"err"=>FALSE);
				}
			}
			if ($v6) return query($domain,false);
			return array("answer"=>"NO_RESULT","err"=>TRUE);
		}
	}
	$dns_query=new DNSQuery($_POST['srv'],intval($_POST['port']),60,!isset($_POST['tcp']));
	$ipv6 = isset($_POST['aaaa']);
	$q = explode(',',$_POST['m']);
	if (!isset($_POST['directout'])) {
		header('Content-Type: application/xml;charset=UTF-8');
		echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?><output>";
		for ($i=0;$i<count($q);){
			$id = $q[$i++];
			$domain = $q[$i++];
			$answer = query($domain,$ipv6);
			if ($answer["err"])
				echo "<error id=\"{$id}\" err=\"{$answer['answer']}\" />";
			else 
				echo "<item id=\"{$id}\" data=\"{$answer['answer']}\" />";
		}
		echo "</output>";
	} else {
		header('Content-Type: text/plain');
		for ($i=0;$i<count($q);){
			$domain = $q[$i++];
			$answer = query($domain,$ipv6);
			if (!$answer["err"])
				echo "{$answer['answer']}\t{$domain}\n";
		}
	}
	exit;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Hosts Generator</title>
<script language="javascript" src="script.js"></script>
<link href="style.css" rel="stylesheet" type="text/css" />
</head>
<body onload="pageloaded();">
<table width="100%" cellpadding="0" cellspacing="0">
  <tr>
    <th colspan="2" id="header">
    <h1>Hosts Generator</h1>
    Another tool from laobubu lab.
    </th>
  </tr>
  <tr>
    <th scope="row"><h2>概述</h2></th>
    <td><p>本工具将帮助你生成 Hosts 文件。</p>
    <p>你需要做的就是把含有域名的文字（可以是一段网页源码，可以是一个已过时的 Hosts 文件的内容）复制过来并提供相关的设定。</p>
    <p>准备好了？跟随左边的指导继续吧……</p></td>
  </tr>
  <tr>
    <th scope="row"><h2>原始数据</h2></th>
    <td><p>正如前面所述，把含有域名的文字弄过来吧！</p>
    <p>
      <textarea name="input" cols="45" rows="5" class="txtbox" id="input" onchange="inchange();"></textarea>
    </p>
    <ul>
      <li><p>
      <label>把以 # 开头的行
        <select name="opt1" id="opt1" onchange="inchange();">
          <option value="none">不做特殊处理</option>
          <option value="ignore" id="opt1HTML">忽略</option>
          <option value="comment">作为 Hosts 注释保留</option>
          <option value="comment2" selected="selected">作为 Hosts 注释保留 （此外，请将空行也保留）</option>
        </select>
      </label>
      </p>
      </li>
    <li><select name="opt2" id="opt2" onchange="inchange();">
        <option value="none">忽略</option>
        <option value="keep" selected="selected">保留</option>
      </select>含以下内容的行（用半角分号隔开）：
      <input name="txtIgnore" type="text" id="txtIgnore" value="127.0.0.1;::1" size="80" onchange="inchange();" />
    </li>
    </ul></td>
  </tr>
  <tr>
    <th scope="row"><h2>筛选</h2></th>
    <td><p>出现了意外的域名？没关系，选中它们并按删除。</p>
      <table width="100%">
        <tr>
          <td width="60%"><select name="domainlist" size="1" multiple="multiple" class="txtbox" id="domainlist">
          </select></td>
          <td><p>
            <input type="button" value="删除选择项(DEL)" onclick="t_del()" />
          </p>
          <p>
            <input type="button" value="将选择项指向" onclick="t_loc()" />
            <input name="txtIP1" type="text" id="txtIP1" placeholder="留空则为删除指向" value="127.0.0.1" />
          </p>
          <p>
            <input type="button" value="按如下规则选中条目" onclick="t_sel()" /><br />
            <input type="text" id="txtPattern1" placeholder="*.google.com" /><select id="txtPattern1t">
              <option value="wildcard" selected="selected">通配符（如*.com）</option>
              <option value="regex">正则表达式（如^.+com$）</option>
            </select><br />
			<label>
			  <input type="checkbox" name="optR" id="optR" />反选</label>
              <label>
			  <input type="checkbox" name="optC" id="optC" />忽略注释</label>
          </p></td>
        </tr>
    </table></td>
  </tr>
  <tr>
    <th scope="row"><h2>选项</h2></th>
    <td><p>你可以设定 DNS 相关的信息。</p>
    <p>
      <label>DNS 服务器 <input id="txtDNS" type="text" value="8.8.4.4" />
      </label>
        <select id="optDNS" onchange="d_change()">
          <option value="8.8.4.4">Google DNS</option>
          <option value="208.67.222.222">OpenDNS</option>
        </select>
    </p>
    <p>
      <label>端口
        <input type="text" id="txtPort" value="53" />
      </label>
    </p>
    <p>
      <label>
        <input type="checkbox" id="optTCP" />使用 TCP 查询（慢）</label>
    </p>
    <p>
      <label>
        <input type="checkbox" id="optAAAA" />查询 IPv6 地址（AAAA记录）</label>
    </p></td>
  </tr>
  <tr>
    <th scope="row"><h2>开始</h2>亦或是结束</th>
    <td><button id="go" onclick="start()">提交查询请求</button>
    <p id="output_hidden" style="display:none">
      <textarea class="txtbox" id="output"></textarea>
      <button id="advanced_skill_show" onclick="adv_show()">使用 POST 直接获取生成的 Hosts 文件</button>
    </p></td>
  </tr>
  <tr id="advanced_skill" style="display:none">
    <th scope="row"><h2>高端玩法</h2>
    <p>(附加)</p></th>
    <td>
    <p>你可以通过 POST 以下数据到该页 URL&nbsp;以获取简化的 Hosts 文件。解析失败的地址将不会出现在结果中。</p>
    <p><button onclick="start2()" id="go2">试一试</button> 注意 --header="Content-Type: application/x-www-form-urlencoded"</p>
    <p>
      <input type="text" style="width:100%" id="args" value="" readonly="readonly" />
    </p></td>
  </tr>
  <tr>
    <th colspan="2">
    <h1>Fin.</h1>
    <hr />
    <p>External Class: <a href="http://www.purplepixie.org/phpdns/">PHPDNS</a></p>
    <p><a href="http://laobubu.net">laobubu.net</a> | @laobubu</p></th>
  </tr>
</table>
</body>
</html>