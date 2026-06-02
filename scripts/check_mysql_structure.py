#!/usr/bin/env python3
"""
Читает параметры из r1riepasclone/db_access.txt и выводит структуру указанных таблиц MySQL.

Формат db_access.txt (по строке на поле):
  host: ...
  user: ...
  pass: ...
  db: ...
  port: 3306   # необязательно

  pip install -r requirements-db.txt
  python scripts/check_mysql_structure.py
  python scripts/check_mysql_structure.py auto_stock auto_tires

Не коммитьте db_access.txt с реальными паролями.
"""

from __future__ import annotations

import argparse
import re
import sys
from pathlib import Path


def load_db_access(path: Path) -> dict[str, str]:
    text = path.read_text(encoding="utf-8", errors="replace")
    cfg: dict[str, str] = {}
    for raw in text.splitlines():
        line = raw.strip()
        if not line or line.startswith("#"):
            continue
        m = re.match(r"^(\w+)\s*:\s*(.*)$", line)
        if not m:
            continue
        key, val = m.group(1).lower(), m.group(2).strip()
        cfg[key] = val
    required = ("host", "user", "pass", "db")
    missing = [k for k in required if k not in cfg]
    if missing:
        raise SystemExit(f"В {path} не хватает полей: {', '.join(missing)}")
    if "port" not in cfg:
        cfg["port"] = "3306"
    return cfg


def main() -> None:
    parser = argparse.ArgumentParser(description="Проверка структуры таблиц MySQL")
    parser.add_argument(
        "tables",
        nargs="*",
        default=["auto_stock", "auto_tires"],
        help="Имена таблиц (по умолчанию: auto_stock auto_tires)",
    )
    parser.add_argument(
        "--access",
        type=Path,
        default=None,
        help="Путь к db_access.txt (по умолчанию: ../db_access.txt от этого скрипта)",
    )
    args = parser.parse_args()

    script_dir = Path(__file__).resolve().parent
    access_path = args.access or (script_dir.parent / "db_access.txt")
    if not access_path.is_file():
        raise SystemExit(f"Файл не найден: {access_path}")

    cfg = load_db_access(access_path)

    try:
        import pymysql
    except ImportError:
        print("Установите зависимость: pip install pymysql", file=sys.stderr)
        raise SystemExit(1) from None

    conn = pymysql.connect(
        host=cfg["host"],
        port=int(cfg["port"]),
        user=cfg["user"],
        password=cfg["pass"],
        database=cfg["db"],
        charset="utf8mb4",
        cursorclass=pymysql.cursors.DictCursor,
    )

    with conn:
        with conn.cursor() as cur:
            for table in args.tables:
                print(f"\n{'=' * 72}\nTABLE: {table}\n{'=' * 72}")
                cur.execute(
                    """
                    SELECT TABLE_SCHEMA, TABLE_NAME, ENGINE, TABLE_COLLATION,
                           CREATE_OPTIONS, TABLE_COMMENT
                    FROM information_schema.TABLES
                    WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s
                    """,
                    (cfg["db"], table),
                )
                meta = cur.fetchone()
                if not meta:
                    print(f"(таблица `{table}` не найдена в БД `{cfg['db']}`)\n")
                    continue
                for k, v in meta.items():
                    print(f"  {k}: {v}")

                print("\n--- COLUMNS (information_schema) ---")
                cur.execute(
                    """
                    SELECT ORDINAL_POSITION, COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE,
                           COLUMN_DEFAULT, COLUMN_KEY, EXTRA, COLUMN_COMMENT
                    FROM information_schema.COLUMNS
                    WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s
                    ORDER BY ORDINAL_POSITION
                    """,
                    (cfg["db"], table),
                )
                rows = cur.fetchall()
                for r in rows:
                    print(
                        f"  {r['ORDINAL_POSITION']:3}  {r['COLUMN_NAME']:<24} "
                        f"{r['COLUMN_TYPE']:<28} NULL={r['IS_NULLABLE']:<3} "
                        f"KEY={r['COLUMN_KEY'] or '-':<3} {r['EXTRA'] or ''}"
                    )
                    if r["COLUMN_COMMENT"]:
                        print(f"       comment: {r['COLUMN_COMMENT']}")

                print("\n--- CREATE TABLE ---")
                cur.execute(f"SHOW CREATE TABLE `{table}`")
                row = cur.fetchone()
                # pymysql DictCursor: ключ может быть 'Create Table' или подобное
                create_sql = None
                if row:
                    for v in row.values():
                        if isinstance(v, str) and "CREATE TABLE" in v.upper():
                            create_sql = v
                            break
                if create_sql:
                    print(create_sql)
                else:
                    print(row)

                print("\n--- INDEXES ---")
                cur.execute(
                    """
                    SELECT INDEX_NAME, NON_UNIQUE, SEQ_IN_INDEX, COLUMN_NAME, COLLATION,
                           CARDINALITY, INDEX_TYPE
                    FROM information_schema.STATISTICS
                    WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s
                    ORDER BY INDEX_NAME, SEQ_IN_INDEX
                    """,
                    (cfg["db"], table),
                )
                for r in cur.fetchall():
                    print(
                        f"  {r['INDEX_NAME']:<32} uniq={r['NON_UNIQUE']}  "
                        f"col={r['COLUMN_NAME']}  type={r['INDEX_TYPE']}"
                    )

    print("\nГотово.")


if __name__ == "__main__":
    main()
