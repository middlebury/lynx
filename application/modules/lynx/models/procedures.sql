-- Clear out old versions of the procedures and triggers.
DROP FUNCTION IF EXISTS get_mark_fulltext;
DROP PROCEDURE IF EXISTS update_mark_fulltext;
DROP PROCEDURE IF EXISTS update_all_fulltext;


DELIMITER |

-- Combine all of our full-text string bulding into one function.
CREATE FUNCTION get_mark_fulltext (mark_id INT) RETURNS TEXT
BEGIN
    DECLARE mark_fulltext TEXT;
    DECLARE next_tag VARCHAR(50);
    DECLARE tag_cursor CURSOR FOR SELECT tag FROM tag WHERE fk_mark = mark_id;
    
    -- Add combine the description and notes
    SELECT CONCAT(description, '\n', notes, '\n', url) INTO mark_fulltext FROM mark INNER JOIN url ON fk_url = url.id WHERE mark.id = mark_id;
    
    
    -- Add on all of the tag strings
    OPEN tag_cursor;
    
    BEGIN
        DECLARE EXIT HANDLER FOR NOT FOUND BEGIN END;
        LOOP
            FETCH tag_cursor INTO next_tag;
            SELECT CONCAT(mark_fulltext, '\n', REPLACE(next_tag, '_', ' ')) INTO mark_fulltext;
        END LOOP;
    END;
    
    CLOSE tag_cursor;
    
    RETURN mark_fulltext;
END |

-- Update a full-text string.
CREATE PROCEDURE update_mark_fulltext (IN mark_id INT)
BEGIN
    
    DELETE FROM mark_fulltext WHERE fk_mark = mark_id;
    
    INSERT INTO mark_fulltext (fk_mark, mark_fulltext) VALUES (mark_id, get_mark_fulltext(mark_id));
    
END |

CREATE PROCEDURE update_all_fulltext ()
BEGIN
    DECLARE next_id INT;
    DECLARE mark_cursor CURSOR FOR SELECT id FROM mark;
    
    OPEN mark_cursor;
    
    BEGIN
        DECLARE EXIT HANDLER FOR NOT FOUND BEGIN END;
        LOOP
            FETCH mark_cursor INTO next_id;
            CALL update_mark_fulltext(next_id);
        END LOOP;
    END;
    
    CLOSE mark_cursor;
    
END |

DELIMITER ;
