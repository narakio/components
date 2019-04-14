<?php namespace Naraki\Core\Support\Database;

class MysqlRawQueries extends RawQueries
{
    public function triggerUserFullName(){
        \DB::unprepared('
                CREATE TRIGGER t_people_create_fullname BEFORE INSERT ON people
                  FOR EACH ROW
                BEGIN
                  IF NEW.full_name IS NULL THEN
                    SET NEW.full_name = CONCAT(NEW.first_name, " ", NEW.last_name);
                    END IF;
                  END;
        ');
        \DB::unprepared('
                CREATE TRIGGER t_people_update_fullname BEFORE UPDATE ON people
                    FOR EACH ROW 
                    BEGIN
                      SET NEW.full_name = CONCAT(NEW.first_name, " ", NEW.last_name);
                    END;
        ');
    }
}