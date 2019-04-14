<?php namespace Naraki\Core\Support\Database;

class SqliteRawQueries extends RawQueries
{

    public function triggerUserFullName()
    {
        \DB::unprepared('
                CREATE TRIGGER t_people_create_fullname after INSERT ON people
                    FOR EACH ROW 
                    BEGIN
                      update people SET full_name = NEW.first_name|| " " || NEW.last_name;
                    END
        ');
        \DB::unprepared('
                CREATE TRIGGER t_people_update_fullname after UPDATE ON people
                    FOR EACH ROW
                    BEGIN
                      update people SET full_name = NEW.first_name|| " " || NEW.last_name;
                    END
        ');
    }

}