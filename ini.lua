URL_PATH_ALL=URL_PATH_ALL or {}
local one_path={}
one_path.update = "http://1.12.181.206/"
one_path.voice="http://1.12.181.206:89/voice"
function URL_PATH_ALL.get(PLATFORM_NAME)
    return one_path
end
UPDATE_VERSION_MAX=100
function get_servers_url(account, PLATFORM_NAME, channel,last_srv_id, id1, id2)
	return "http://1.12.181.206:89/serverlist.php";
end