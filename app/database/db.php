<?php

//session_start();
require 'connect.php';

/** 
 * функция для тестового вывода значения на экран
 */
function tt($value)
{
	echo '<pre>';
	print_r($value);
	echo '</pre>';
	exit();
}
function tte($value)
{
	echo '<pre>';
	print_r($value);
	echo '</pre>';
}
/** 
 * Функция для проверки на ошибки выполнения запроса к БД
 */
function dbCheckError($query)
{
	$errInfo = $query->errorInfo();
	if ($errInfo[0] !== PDO::ERR_NONE) {
		echo $errInfo[2];
		exit();
	}
	return true;
}

//--------------------------------------------------------------------------------------------------------------------//

/**
 * функция создаёт запрос на получение данных из одной таблицы: $table
 */
function selectAll($table, $params = [])
{
	// обращаемся к глобальной переменной (экземпляру класса)
	global $pdo;
	// составляем запрос
	$sql = "SELECT * FROM $table";

	if (!empty($params)) {
		$i = 0;
		foreach ($params as $key => $value) {
			if (!is_numeric($value)) {
				$value = "'" . $value . "'";
			}
			if ($i === 0) {
				$sql = $sql . " WHERE $key=$value";
			} else {
				$sql = $sql . " AND $key=$value";
			}
			$i++;
		}
	}
	// подготовка запроса
	$query = $pdo->prepare($sql);
	// выполнение запроса
	$query->execute();
	// проверка на ошибки
	dbCheckError($query);
	// возвращаем результат запроса
	return $query->fetchAll();
}


/**
 * функция создаёт запрос на получение одной строки из выбранной таблицы: $table
 */
function selectOne($table, $params = [])
{
	global $pdo;
	$sql = "SELECT * FROM $table";

	if (!empty($params)) {
		$i = 0;
		foreach ($params as $key => $value) {
			if (!is_numeric($value)) {
				$value = "'" . $value . "'";
			}
			if ($i === 0) {
				$sql = $sql . " WHERE $key=$value";
			} else {
				$sql = $sql . " AND $key=$value";
			}
			$i++;
		}
	}

	$query = $pdo->prepare($sql);
	$query->execute();
	dbCheckError($query);
	return $query->fetch(); // выводится одна строка
}

//---------------------------------------------------------------------------------------------------------------------//

/** 
 * функция для вставки строки (записи) в таблицу
 */
function insert($table, $params)
{
	global $pdo;
	$i = 0;
	$coll = ''; // в переменной будем формировать ключи для запроса
	$mask = ''; // в переменной укажем соответствующие им значения
	foreach ($params as $key => $value) {
		if ($i === 0) {
			$coll = $coll . "$key";
			$mask = $mask . "'" . "$value" . "'";
		} else {
			$coll = $coll . ", $key";
			$mask = $mask . ", '" . "$value" . "'";
		}
		$i++;
	}

	$sql = "INSERT INTO $table ($coll) VALUES ($mask)";

	$query = $pdo->prepare($sql);
	$query->execute($params);
	dbCheckError($query);
	return $pdo->lastInsertId();
}

//-------------------------------------------------------------------------------------------------------------------//

/** 
 * функция для обновления данных в таблице
 */
function update($table, $id, $params)
{
	global $pdo;
	$i = 0;
	$str = '';
	foreach ($params as $key => $value) {
		if ($i === 0) {
			$str = $str . $key . " = '" . $value . "'";
		} else {
			$str = $str . ", " . $key . " = '" . $value . "'";
		}
		$i++;
	}

	$sql = "UPDATE $table SET $str WHERE id = $id";
	$query = $pdo->prepare($sql);
	$query->execute($params);
	dbCheckError($query);
}

//--------------------------------------------------------------------------------------------------------------------//

/** 
 * функция для удаления строки (записи) в таблице
 */
function delete($table, $id)
{
	global $pdo;

	$sql = "DELETE FROM $table WHERE id =" . $id;
	$query = $pdo->prepare($sql);
	$query->execute();
	dbCheckError($query);
}

//--------------------------------------------------------------------------------------------------------------------//

// Выборка записей (posts) с автором в админку
function selectAllFromPostsWithUsers($table1, $table2)
{
	global $pdo;
	$sql = "SELECT 
        t1.id,
        t1.title,
        t1.img,
        t1.content,
        t1.status,
        t1.id_topic,
        t1.created_date,
        t2.username
        FROM $table1 AS t1 JOIN $table2 AS t2 ON t1.id_user = t2.id";
	$query = $pdo->prepare($sql);
	$query->execute();
	dbCheckError($query);
	return $query->fetchAll();
}

// Выборка записей (posts) с автором на главную
function selectAllFromPostsWithUsersOnIndex($table1, $table2, $limit, $offset)
{
	global $pdo;
	$sql = "SELECT p.*, u.username FROM $table1 AS p JOIN $table2 AS u ON p.id_user = u.id WHERE p.status=1 LIMIT $limit OFFSET $offset";
	$query = $pdo->prepare($sql);
	$query->execute();
	dbCheckError($query);
	return $query->fetchAll();
}

// Выборка записей (posts) с автором на главную
function selectTopTopicFromPostsOnIndex($table1)
{
	global $pdo;
	$sql = "SELECT * FROM $table1 WHERE id_topic = 18";
	$query = $pdo->prepare($sql);
	$query->execute();
	dbCheckError($query);
	return $query->fetchAll();
}


// Поиск по заголовкам и содержимому (простой)
function seacrhInTitileAndContent($text, $table1, $table2)
{
	$text = trim(strip_tags(stripcslashes(htmlspecialchars($text))));
	global $pdo;
	$sql = "SELECT 
        p.*, u.username 
        FROM $table1 AS p 
        JOIN $table2 AS u 
        ON p.id_user = u.id 
        WHERE p.status=1
        AND p.title LIKE '%$text%' OR p.content LIKE '%$text%'";
	$query = $pdo->prepare($sql);
	$query->execute();
	dbCheckError($query);
	return $query->fetchAll();
}

// Выборка записи (posts) с автором для синг
function selectPostFromPostsWithUsersOnSingle($table1, $table2, $id)
{
	global $pdo;
	$sql = "SELECT p.*, u.username FROM $table1 AS p JOIN $table2 AS u ON p.id_user = u.id WHERE p.id=$id";
	$query = $pdo->prepare($sql);
	$query->execute();
	dbCheckError($query);
	return $query->fetch();
}

// Считаем количество строк в таблице
function countRow($table)
{
	global $pdo;
	$sql = "SELECT Count(*) FROM $table";
	$query = $pdo->prepare($sql);
	$query->execute();
	dbCheckError($query);
	return $query->fetchColumn();
}
