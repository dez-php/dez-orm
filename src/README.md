# DezORM

## CRUD

#### Выбор всех записей из таблицы с дальнейшим обновлением

```php

    use Dez\ORM\Model\Table as TableModel;

    class UserModel extend TableModel {
        // установите имя таблицы базы данных
        // Обязательный параметр
        static $table = 'users';
    }

    // получаем коллекцию моделей UserModel
    $users = UserModel::all();

    // к примеру надо установить всем пользователям одно и тоже имя
    foreach( $users as $user ) {
        // назначаем имя Джон для поля user_name
        $user->setUserName( 'Джон' );
    }

    // также можно использовать методы коллекции для прохождений по каждой модели
    $users->each( function( $i, $user ) {
        $user->setUserName( 'John' );
    } );

    // обновляем всех пользователей
    $users->save();

```

#### Использование условий

```php

    // объект составляющий запрос для результата
    $usersQueryBuilder = UserModel::query();

    // find - возвращает коллекцию моделей результата
    $users = $usersQueryBuilder
        ->whereStatus( 'active' )
        ->whereLastVisit( ( new \DateTime( '- 15 days' )->format( 'Y-m-d H:i:s' ) ), '<' )
        ->orderUserName( 'DESC' )
        ->find();

```

#### Вставка записей в таблицу

```php

    // массив данных
    $data   = [
        'user_name'     => 'John',
        'password'      => md5( 'user_password' )
    ];

    // делаем вставку при помощи статического метода insert
    $user   = UserModel::insert( $data );

    // видим значение pk ключа
    die( var_dump( $user->id() ) );

    // второй способ
    // создание постой модели
    $user   = new UserModel();

    $user
        ->setUserName( 'John' )
        ->setPassword( md5( 'user_password' ) )
        ->save();

    // видим значение pk ключа
    die( var_dump( $user->id() ) );

```

#### Удаление записей

```php

    // удаление всех записей
    $users  = UserModel::all();
    $users->delete();
    // или
    UserModel::all()->delete();
    UserModel::query()->whereUserGroupId( 5 )->delete();

    // для одной записи
    $user   = UserModel::one( 17 );
    $user->delete();
    // или
    UserModel::query()->whereUserGroupId( 5 )->first()->delete();

```