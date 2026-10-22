<?php
// Keeps the sessions in the database instead of in files.
//
// Why? On a normal server (XAMPP, Hostinger) the normal PHP sessions are fine
// and a bit faster. But on a serverless host like Vercel every request can
// land on a different machine, and the files on that machine disappear. Then
// the shopping cart is empty again on the next page and nobody stays logged in.
//
// Putting the sessions in the database fixes that, because the database is
// shared by every machine.
//
// The functions below use the old style of session_set_save_handler, so there
// is no class - the project stays procedural.

// Open the session (nothing to do, the connection is already open)
function session_open_db($save_path, $session_name)
{
    return true;
}

// Read the session data out of the database
function session_read_db($id)
{
    global $conn;

    $result = mysqli_query($conn, "SELECT data FROM sessions WHERE id = '" . mysqli_real_escape_string($conn, $id) . "'");

    if ($row = mysqli_fetch_assoc($result)) {
        return $row['data'];
    }

    return '';
}

// Save the session data in the database
function session_write_db($id, $data)
{
    global $conn;

    $safe_id = mysqli_real_escape_string($conn, $id);
    $safe_data = mysqli_real_escape_string($conn, $data);
    $now = time();

    // The session might be new, so we try an update first and add it when that
    // does not work
    $result = mysqli_query($conn, "UPDATE sessions SET data = '$safe_data', modified = $now WHERE id = '$safe_id'");

    if (mysqli_affected_rows($conn) === 0) {
        mysqli_query($conn, "INSERT INTO sessions (id, data, modified) VALUES ('$safe_id', '$safe_data', $now)
                              ON DUPLICATE KEY UPDATE data = '$safe_data', modified = $now");
    }

    return true;
}

// Close the session (nothing to do)
function session_close_db()
{
    return true;
}

// Delete one session
function session_destroy_db($id)
{
    global $conn;

    mysqli_query($conn, "DELETE FROM sessions WHERE id = '" . mysqli_real_escape_string($conn, $id) . "'");

    return true;
}

// Delete the sessions that are too old
function session_gc_db($max_lifetime)
{
    global $conn;

    mysqli_query($conn, "DELETE FROM sessions WHERE modified < " . (time() - $max_lifetime));

    // How many were removed (the interface wants a number here)
    return mysqli_affected_rows($conn);
}

// PHP 8.4 and up want an object for the session handler.
// This class is only a thin wrapper: the work is done by the functions
// above, so the rest of the project stays procedural.
class DbSessionHandler implements SessionHandlerInterface
{
    public function open($path, $name)
    {
        return session_open_db($path, $name);
    }

    public function close()
    {
        return session_close_db();
    }

    public function read($id)
    {
        return session_read_db($id);
    }

    public function write($id, $data)
    {
        return session_write_db($id, $data);
    }

    public function destroy($id)
    {
        return session_destroy_db($id);
    }

    public function gc($max_lifetime)
    {
        return session_gc_db($max_lifetime);
    }
}

// Turn the database session handler on
function use_database_sessions()
{
    session_set_save_handler(new DbSessionHandler());
}
