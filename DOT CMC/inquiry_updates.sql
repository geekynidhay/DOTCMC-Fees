-- Create the inquiries table
CREATE TABLE IF NOT EXISTS inquiries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    mobile VARCHAR(20) NOT NULL,
    course VARCHAR(100) NOT NULL,
    fees VARCHAR(50) NOT NULL,
    session VARCHAR(50) NOT NULL,
    date_created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Add media URLs to whatsapp_queue
ALTER TABLE whatsapp_queue
ADD COLUMN media_url1 VARCHAR(500) DEFAULT NULL AFTER message,
ADD COLUMN media_url2 VARCHAR(500) DEFAULT NULL AFTER media_url1;
