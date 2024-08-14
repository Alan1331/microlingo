DELIMITER $$

CREATE TRIGGER assign_sort_id_before_insert
BEFORE INSERT ON learning_units
FOR EACH ROW
BEGIN
    DECLARE new_sort_id INT;
    
    -- Find the lowest available sortId starting from 1
    SET new_sort_id = 1;
    
    WHILE EXISTS (SELECT 1 FROM learning_units WHERE sortId = new_sort_id) DO
        SET new_sort_id = new_sort_id + 1;
    END WHILE;
    
    -- Assign the calculated sortId to the new row
    SET NEW.sortId = new_sort_id;
END $$

DELIMITER ;
