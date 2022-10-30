## Сравнение товаров из различных онлайн-магазинов :shopping:	
### :information_source: О проекте

>  Проект предоставляет пользователям платные и бесплатные услуги сбора, анализа и визуализации данных о товарах из различных интернет-магазинов, позволяя выбрать наиболее интересные и выгодные варианты :heavy_dollar_sign:

![-----------------------------------------------------](https://raw.githubusercontent.com/andreasbm/readme/master/assets/lines/dark.png)


### :desktop_computer: Технические требования

* Apache 2.4
* PHP 7.2 - 7.4
* MySQL 5.7

![-----------------------------------------------------](https://raw.githubusercontent.com/andreasbm/readme/master/assets/lines/dark.png)

### :gear: Развертывание проекта

<details>
   <summary>Показать</summary>

* Клонируйте данный репозиторий в свою рабочую область
* Перейдите в корень проекта и выполните команду `$ composer install`
* Примечание: для работы с базой данных необходимо изменить параметр sql_mode в .ini файле MySQL'а 
* Пример расположения .ini файла в OpenServer: `C:\Dev\OpenServer\userdata\config\MySQL-5.7-Win10_my.ini` 

  После `[mysqld]` добавьте следующую строку \
  `sql_mode = "STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION"`

* Создайте базу данных и выполните в ней SQL-файл `checker.sql`
* Дополнительно создайте базы данных для каждого источника товаров: `google`, `china`, `ebay` \
и выполните в них соответствующие SQL-файлы.
* Настройте подключение к базе данных в файле ```/common/config/main-local.php```	
* В корне проекта выполните команду `php init`
  
</details>


![-----------------------------------------------------](https://raw.githubusercontent.com/andreasbm/readme/master/assets/lines/dark.png)

### :open_book:	Словарь, терминология

<details>
  <summary>Показать</summary>
  
  <table>
    <thead>
      <th>
      Название (рус.)
      </th>
      <th>
       Название (англ.)
      </th>
    </thead>
    <tbody>
      <tr>
        <td>Товар</td>
        <td>Product</td>
      </tr>
    </tbody>
  </table>
  
</details>



![-----------------------------------------------------](https://raw.githubusercontent.com/andreasbm/readme/master/assets/lines/dark.png)

### :key: Логины и пароли для входа:
* Суперадмин - `admin`, `admin-password`
* Пользователь - `tiubum`, `simplest`
