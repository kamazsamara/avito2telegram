-- phpMyAdmin SQL Dump
-- version 4.2.12deb2+deb8u2
-- http://www.phpmyadmin.net
--
-- Хост: localhost
-- Время создания: Июн 10 2019 г., 20:26
-- Версия сервера: 5.5.54-0+deb8u1
-- Версия PHP: 5.6.33-0+deb8u1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- База данных: `avito`
--

-- --------------------------------------------------------

--
-- Структура таблицы `av_cars`
--

CREATE TABLE IF NOT EXISTS `av_cars` (
`id` int(11) NOT NULL,
  `link` varchar(255) NOT NULL,
  `desc` text NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=44447 DEFAULT CHARSET=utf8;

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `av_cars`
--
ALTER TABLE `av_cars`
 ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `av_cars`
--
ALTER TABLE `av_cars`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=44447;