<?php
/**
 * User: lixiang4u
 * Date: 2017/6/28 14:35
 *
 */
class MySqlHelp {
	private $host;
	private $username;
	private $password;
	private $dbName;
	private $port;
	private $mysqli;

	public function __construct() {
		//ini_set('memory_limit', '2048M');
		//set_time_limit(0);
		$this->connect();
	}

	/**
	 * 执行入口
	 *
	 * @param string $name MySQL帮助的名称
	 *
	 * @return string
	 */
	public function Run($name = 'CONTENTS') {
		$menus                 = $this->queryHelp($name);
		$helpData              = $this->getContent($menus);
		$helpData['SeverInfo'] = $this->parseServerInfo();
		return $this->RenderHtmlDocument($helpData);
	}


	/**
	 * 连接数据库，不需要真正的用户密码
	 * @throws Exception
	 */
	public function connect() {
		$this->mysqli = new mysqli(
			$this->host,
			$this->username,
			$this->password,
			$this->dbName,
			$this->port
		);
		$this->mysqli->connect();
		if ( $this->mysqli->connect_errno ) {
			throw new Exception($this->mysqli->connect_error);
		}
	}

	/**
	 * 解析MySQL的服务器信息
	 * @return array
	 */
	public function parseServerInfo() {
		$info = $this->mysqli->server_info;
		$info = array_merge(array($info), explode('-', $info));
		return $info;
	}

	/**
	 * 查询Help文档
	 *
	 * @param string $name
	 *
	 * @return array
	 */
	public function queryHelp($name = 'CONTENTS') {
		$data  = array();
		$query = $this->mysqli->query("HELP '{$name}';");
		while ($row = $query->fetch_assoc()) {
			$data[] = $row;
		}
		return $data;
	}

	/**
	 * 获取帮助文档的连接列表或者文档明细
	 *
	 * @param array $menus
	 *
	 * @return array
	 */
	public function getContent($menus = array()) {
		$data = array('IsCategory' => true, 'Menus' => array(), 'Description' => '', 'Example' => '');
		foreach ($menus as $menu) {
			if ( isset($menu['description']) ) {
				$data['IsCategory']  = false;
				$data['Menus']       = array();
				$data['Description'] = $menu['description'];
				$data['Example']     = $menu['example'];
				break;
			}
			break;
		}
		if ( $data['IsCategory'] === true ) {
			$data['Menus'] = array_column($menus, 'name');
		}
		return $data;
	}

	/**
	 * 渲染最终的html
	 *
	 * @param $data
	 *
	 * @return string
	 */
	public function RenderHtmlDocument($data) {
		$htmlLinks = '';
		foreach ($data['Menus'] as $key => $menu) {
			$htmlLinks .= sprintf('<li><a href="?name=%s">%s. %s</a></li>%s', $menu, $key + 1, $menu, "\r\n");
		}
		$display = $data['IsCategory'] == false ? 'block' : 'none';
		$html
				 = <<<EOL
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"/>
    <title>MySQLHelp</title>
    <style type="text/css">
        a.menu {
            line-height: 150%;
        }

        a.menu,
        div.description,
        div.example {
            font-weight: bold;
            font-size: 110%;
        }

        body{
            margin: 0;
            font-family: "Levenim MT", "Ubuntu Light", "微软雅黑";
        }

        .blank{
            width: 100%;
            height: 20px;
        }
        .container{
            width: 80%;
            margin: 0 auto;
        }
        .head{
            width: 100%;
            height:50px;
            background-color: #101010;
            border-width: 0 0 1px;
            color: #9d9d9d;
            cursor: pointer;
        }
        .head:hover{
            color: #f1fff1;
        }
        .side-bar{
            width: 260px;
            float: left;
        }
        .main{
            min-height: 450px;
            overflow: hidden;/** 控制文字环绕的效果 **/
        }
        .footer{
            color: #c3c3c3;
            line-height: 180%;
            clear: both;
            padding: 20px 0 20px 0;
            position: fixed;
            bottom: 0;
        }


        .head .desc{
            line-height: 50px;
            font-size: 18px;
            font-weight: bold;
        }
        .head .version{
            float: right;
            line-height: 50px;
            color: #9d9d9d;
        }
        .head .version:hover{
            color: #9d9d9d;
        }
        .side-bar ul{
            list-style: none;
            margin: 0;
            /*padding:0;*/
            line-height: 180%;
            padding: 15px 0 15px 0;
        }
        .side-bar li{
            padding:  0 10px 0 10px;
        }
        .side-bar a{
            color: #0074a3;
            text-decoration: none;
        }
        .side-bar a:hover{
            text-decoration: underline;
        }

        #m-string{ color: #c3c3c3; }
        #m-light{ font-weight: bold; color: #000000; }

    </style>
</head>
<body>

<div class="head">
    <div class="container">
        <a class="version">MySQL Version: {$data['SeverInfo'][0]}</a>
        <div class="desc">MySQL {$data['SeverInfo'][1]} Reference Manual</div>
    </div>
</div>
<div class="container">
    <div class="blank"></div>
    <div class="side-bar">
        <ul>
            <li><a href="?from=http://github.com/lixiang4u">MySQL> HELP 'CONTENTS';</a></li>
            {$htmlLinks}
        </ul>
    </div>
    <div class="main" style="display: {$display};">
        <div class="blank"></div>
        <div>
        	<div class="description">Description:</div>
        	<pre>{$data['Description']}</pre>
        </div>
        <div>
        	<div class="example">Example:</div>
        	<pre>{$data['Example']}</pre>
        </div>
    </div>
    <div class="blank"></div>
    <div class="blank"></div>
    <div class="blank"></div>
    <div class="blank"></div>
    <div class="footer">
        <span id="m-string"></span>
        <span id="m-light" class="c-light">_</span>
    </div>
</div>

<script type="text/javascript">
    var split = '&nbsp;&nbsp;';
    var string = document.getElementById('m-string');
    var light = document.getElementById('m-light');
    var marqueeString = '';
    var marqueeIndex = 0;
    var marqueePool = [
        'M', 'y', 'S', 'Q', 'L',
        '>', split,
        'H', 'E', 'L', 'P', split,
        '\'', 'C', 'O', 'N', 'T', 'E', 'N', 'T', 'S', '\'', split,
        ';'
    ];
    setInterval(function () {
        light.style.display == 'none' ?
            (light.style.display = 'inline-block') :
            (light.style.display = 'none');
    }, 400);

    setInterval(function () {
        marqueeIndex >= marqueePool.length ? (marqueeString = '') : false;
        marqueeIndex >= marqueePool.length ? (marqueeIndex = 0) : false;

        marqueeString += marqueePool[marqueeIndex];
        marqueeIndex++;

        string.innerHTML = marqueeString;
    }, 800);

</script>

</body>
</html>
EOL;
		return $html;
	}

}
