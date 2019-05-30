# climbs
Strava climbing segments presented for my city

# To clean up DB:
tr -s -C '[0-9]' '\n' | awk '{printf "delete from climb where id=%d;\n", $0}' | sqlite3 db/climbs.sqlite3

