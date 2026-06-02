#!/usr/bin/env python3
"""Import a phpMyAdmin-style SQL dump into MySQL via pymysql."""

from __future__ import annotations

import argparse
import sys
import time
from pathlib import Path

import pymysql


def iter_statements(path: Path):
    buf: list[str] = []
    for raw in path.open("r", encoding="utf-8", errors="replace"):
        line = raw.rstrip("\n")
        stripped = line.strip()
        if not stripped or stripped.startswith("--"):
            continue
        buf.append(line)
        if stripped.endswith(";"):
            yield "\n".join(buf)
            buf.clear()
    if buf:
        yield "\n".join(buf)


def main() -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument("--host", default="localhost")
    parser.add_argument("--port", type=int, default=3306)
    parser.add_argument("--user", default="r1")
    parser.add_argument("--password", default="password")
    parser.add_argument("--database", default="r1")
    parser.add_argument("--file", default="r1.sql")
    args = parser.parse_args()

    dump_path = Path(args.file)
    if not dump_path.is_file():
        print(f"Dump not found: {dump_path}", file=sys.stderr)
        return 1

    print(f"Connecting to {args.user}@{args.host}:{args.port}/{args.database}")
    conn = pymysql.connect(
        host=args.host,
        port=args.port,
        user=args.user,
        password=args.password,
        database=args.database,
        charset="utf8mb4",
        autocommit=False,
        connect_timeout=10,
    )

    cur = conn.cursor()
    cur.execute("SET NAMES utf8mb4")
    cur.execute("SET FOREIGN_KEY_CHECKS=0")
    cur.execute("SET UNIQUE_CHECKS=0")
    cur.execute("SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO'")

    started = time.time()
    executed = 0
    errors = 0

    print(f"Importing {dump_path} ({dump_path.stat().st_size // 1024 // 1024} MB)...")

    try:
        for statement in iter_statements(dump_path):
            sql = statement.strip()
            if not sql:
                continue
            try:
                cur.execute(sql)
                executed += 1
                if executed % 500 == 0:
                    conn.commit()
                    elapsed = time.time() - started
                    print(f"  {executed} statements, {elapsed:.0f}s")
            except Exception as exc:  # noqa: BLE001 - show import progress
                errors += 1
                preview = " ".join(sql.split())[:160]
                print(f"ERROR #{errors}: {exc}\n  SQL: {preview}", file=sys.stderr)
                if errors >= 20:
                    print("Too many errors, aborting.", file=sys.stderr)
                    conn.rollback()
                    return 2
        conn.commit()
    finally:
        cur.execute("SET FOREIGN_KEY_CHECKS=1")
        cur.execute("SET UNIQUE_CHECKS=1")
        conn.commit()
        cur.close()
        conn.close()

    elapsed = time.time() - started
    print(f"Done: {executed} statements in {elapsed:.1f}s, errors={errors}")
    return 0 if errors == 0 else 2


if __name__ == "__main__":
    raise SystemExit(main())
