<?php

define("TOKEN", "37839ab482a178b818448e65f1eee882d00eb220");

define("TODODIR", $argv[1]);
define("GITHUB", "github.txt");
define("TODO", "todo.txt");

define("NL", "\r\n");

$repositories = array();

$handle = fopen(TODODIR . "/" . GITHUB, "r");
if ($handle) {
  while (($line = fgets($handle)) !== false) {
    $repositories[] = $line;
  }
  fclose($handle);
} else {
  die('error opening github.txt');
}

$relevanttodos = array();

$handle = fopen(TODODIR . "/" . TODO, "r");
if ($handle) {
	$i=1;
  while (($line = fgets($handle)) !== false) {
  	if (preg_match("/https:\/\/github.com\/(.*)\/(.*)\/issues\/[0-9]*/i", $line)) {
    	$relevanttodos[] = array( 'key' => $i, "text" => $line );
  	}
  	$i++;
  }
  fclose($handle);
} else {
  die('error opening todo.txt');
}

function readIssues($repo) {

	$url = 'https://api.github.com/repos/'.$repo.'/issues?access_token='. TOKEN .'&state=all';

	$ch = curl_init($url);

	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array("User-Agent: yatil/todo.txt"));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

	$jissues = curl_exec($ch);

	curl_close($ch);

	$issues = json_decode($jissues);

	$return = array();

	$return['repo'] = $repo;

	foreach ($issues as $issue) {

		$return['issues'][] = array(
			"title" => $issue->title,
			"number" => $issue->number,
			"url" => $issue->html_url,
			"state" => $issue->state
		);
	}

	return $return;
}

function alreadyIn($issue) {
	$todos = $GLOBALS['relevanttodos'];
	foreach ($todos as $todo) {
		if (preg_match("~".addslashes($issue['url'])."$~i", $todo['text'])) {
			return $todo['key'];
		}
	}
	return false;
}

function addIssues($data) {

	$repo = $data['repo'];
	$rep = explode("/", $repo);
	$owner = "@".$rep[0];
	$name = "+".$rep[1];

	echo NL.NL."=== ". $repo ." (".count($data["issues"]).") ===".NL.NL;

	foreach ($data['issues'] as $issue) {
		echo "--- ".$issue[title]." (#".$issue['number'].") [".$issue['state']."] ---".NL;
		$already = alreadyIn($issue);
		$ret = "";
		if ($already && ($issue['state'] == 'closed')) {
			echo "    Issue closed, marking as done".NL;
			passthru(escapeshellcmd("/usr/local/bin/todo.sh do $already"), $ret);
			echo $ret.NL.NL;
		} elseif (!$already && ($issue['state'] == 'open')) {
				echo "    New issue, add to the list".NL;
				passthru(escapeshellcmd("/usr/local/bin/todo.sh add ".utf8_decode($issue[title])." (#".$issue['number'].") $owner $name ".$issue['url']), $ret);
				echo $ret.NL.NL;
		} else {
			echo "    Issue ignored (already closed or open on the list)".NL;
		}
	}

}

$issues = array();

foreach ($repositories as $repo) {
	addIssues(readIssues(trim($repo)));
}

// var_dump($repositories);
// var_dump($issues);
// var_dump($relevanttodos);
