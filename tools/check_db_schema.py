import pymysql
from pymysql.err import OperationalError

configs = [
    dict(host="localhost", port=3306, user="r1", password="password", database="r1", label=".env r1/password"),
    dict(host="localhost", port=3306, user="newr1", password="J569klll", database="r1", label="docker newr1"),
    dict(host="localhost", port=3306, user="root", password="root_password", database="r1", label="docker root"),
    dict(host="localhost", port=3306, user="r1", password="password", database="shopr1riepas", label=".env shopr1riepas"),
    dict(host="localhost", port=3306, user="newr1", password="J569klll", database="shopr1riepas", label="docker shopr1riepas"),
]

booking_tables = [
    "queues",
    "slots",
    "workingdays",
    "new_workingdays",
    "offices",
    "services",
    "users",
    "sessions",
    "migrations",
    "office_mobile_prefs",
]

conn = None
for cfg in configs:
    label = cfg.pop("label")
    try:
        conn = pymysql.connect(**cfg, connect_timeout=5, charset="utf8mb4")
        print(f"OK: {label} -> {cfg['database']}")
        break
    except OperationalError as e:
        print(f"FAIL: {label} -> {e.args[0]} {e.args[1]}")

if not conn:
    raise SystemExit("No working DB credentials")

cur = conn.cursor()
cur.execute("SELECT DATABASE()")
print("Current DB:", cur.fetchone()[0])

cur.execute("SHOW TABLES")
all_tables = sorted(r[0] for r in cur.fetchall())
print(f"\nTotal tables: {len(all_tables)}")
print("All tables:")
for t in all_tables:
    print(" -", t)

print("\nBooking/Laravel tables status:")
for t in booking_tables:
    status = "EXISTS" if t in all_tables else "MISSING"
    print(f" {status:7} {t}")

for t in ["queues", "slots", "offices", "workingdays"]:
    if t in all_tables:
        cur.execute(f"SHOW CREATE TABLE `{t}`")
        create = cur.fetchone()[1]
        print(f"\n--- SHOW CREATE TABLE {t} ---")
        print(create[:2500] + ("..." if len(create) > 2500 else ""))
        cur.execute(f"SELECT COUNT(*) FROM `{t}`")
        print(f"Rows in {t}:", cur.fetchone()[0])

cur.execute("SHOW DATABASES")
dbs = [r[0] for r in cur.fetchall()]
print("\nDatabases on server:", ", ".join(dbs))

conn.close()
