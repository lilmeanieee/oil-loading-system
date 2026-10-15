<?php

namespace App\Support;

/**
 * DEMO ONLY. Sample data for the Operations pages (dashboard, ATL approvals,
 * documents and release, hauler management). The browser keeps its own working
 * copy in localStorage, so approvals and printouts survive page changes.
 * Replace seed() with real queries when the backend exists.
 * Products are limited to R95, R91 and DIESEL.
 */
class OpsData
{
    public const VERSION = 2;

    public static function seed(?string $user = null): array
    {
        $seed = json_decode(self::json(), true);
        $seed['user'] = $user ?: 'M. Reyes';
        if (! in_array($seed['user'], $seed['staff'], true)) {
            array_unshift($seed['staff'], $seed['user']);
        }

        return $seed;
    }

    private static function json(): string
    {
        return <<<'JSON'
{
 "ver": 2,
 "staff": [
  "M. Reyes",
  "J. Lagumbay",
  "A. Dizon",
  "R. Cabahug"
 ],
 "seq": {
  "lt": 347,
  "dr": 347,
  "wc": 347
 },
 "haulers": [
  {
   "id": "H1",
   "name": "Mindanao Fuel Haulers Inc.",
   "contact": "Ramon Dela Cruz",
   "phone": "0917 555 0142",
   "addr": "Brgy. Calumpang, General Santos City",
   "active": true
  },
  {
   "id": "H2",
   "name": "Sarangani Bay Tankers",
   "contact": "Liza Mangubat",
   "phone": "0918 555 0177",
   "addr": "Poblacion, Alabel, Sarangani",
   "active": true
  },
  {
   "id": "H3",
   "name": "Soccsksargen Petro Logistics",
   "contact": "Eduardo Tan",
   "phone": "0922 555 0119",
   "addr": "Zone III, Koronadal City",
   "active": true
  },
  {
   "id": "H4",
   "name": "Pacific Gulf Transport",
   "contact": "Marites Ong",
   "phone": "0929 555 0164",
   "addr": "Brgy. Labangal, General Santos City",
   "active": true
  }
 ],
 "drivers": [
  {
   "id": "D1",
   "name": "Rolando Baculi",
   "lic": "N03-18-004521",
   "exp": "2028-04-12",
   "phone": "0917 301 2288",
   "h": "H1"
  },
  {
   "id": "D2",
   "name": "Jun-jun Salazar",
   "lic": "N03-15-110984",
   "exp": "2027-09-30",
   "phone": "0918 442 9031",
   "h": "H2"
  },
  {
   "id": "D3",
   "name": "Arnel Pascua",
   "lic": "N03-12-076630",
   "exp": "2026-11-03",
   "phone": "0917 220 6145",
   "h": "H1"
  },
  {
   "id": "D4",
   "name": "Michael Tuazon",
   "lic": "N11-19-032218",
   "exp": "2029-01-18",
   "phone": "0922 618 7740",
   "h": "H3"
  },
  {
   "id": "D5",
   "name": "Dennis Villareal",
   "lic": "N03-20-188075",
   "exp": "2028-12-05",
   "phone": "0918 905 3312",
   "h": "H2"
  },
  {
   "id": "D6",
   "name": "Cesar Dumaguit",
   "lic": "N03-14-059412",
   "exp": "2027-03-22",
   "phone": "0929 774 1056",
   "h": "H4"
  },
  {
   "id": "D7",
   "name": "Reynaldo Cuyugan",
   "lic": "N11-17-090233",
   "exp": "2027-08-14",
   "phone": "0922 340 8821",
   "h": "H3"
  },
  {
   "id": "D8",
   "name": "Allan Pimentel",
   "lic": "N03-16-044718",
   "exp": "2028-02-09",
   "phone": "0929 118 4470",
   "h": "H4"
  }
 ],
 "trucks": [
  {
   "plate": "MAB 4721",
   "h": "H1",
   "d": "D1",
   "comps": [
    4000,
    4000,
    4000,
    4000,
    4000
   ],
   "cal": "2027-02-14",
   "lto": "2027-05-31",
   "saf": "2027-01-09",
   "s": "D3"
  },
  {
   "plate": "NDC 8830",
   "h": "H2",
   "d": "D2",
   "comps": [
    5000,
    5000,
    5000,
    5000
   ],
   "cal": "2027-03-02",
   "lto": "2026-12-18",
   "saf": "2027-02-20",
   "s": ""
  },
  {
   "plate": "KAD 1956",
   "h": "H1",
   "d": "D3",
   "comps": [
    6000,
    6000,
    6000
   ],
   "cal": "2026-10-28",
   "lto": "2027-04-11",
   "saf": "2027-03-15",
   "s": "D1"
  },
  {
   "plate": "LAC 3307",
   "h": "H3",
   "d": "D4",
   "comps": [
    4000,
    4000,
    4000,
    4000,
    4000,
    4000
   ],
   "cal": "2027-06-19",
   "lto": "2027-08-02",
   "saf": "2027-06-30",
   "s": "D7"
  },
  {
   "plate": "NBK 6642",
   "h": "H2",
   "d": "D5",
   "comps": [
    3000,
    3000,
    3000,
    3000
   ],
   "cal": "2027-01-25",
   "lto": "2027-02-28",
   "saf": "2026-12-31",
   "s": ""
  },
  {
   "plate": "PHR 2218",
   "h": "H4",
   "d": "D6",
   "comps": [
    4000,
    4000,
    4000,
    4000,
    4000
   ],
   "cal": "2026-09-15",
   "lto": "2027-07-07",
   "saf": "2027-04-23",
   "s": "D8"
  }
 ],
 "atl": [
  {
   "id": "ATL-2026-1051",
   "ctl": "CTL-261014-111",
   "date": "2026-10-14",
   "st": "pending",
   "sync": "synced",
   "saved": "",
   "submitted": "08:12",
   "review": "",
   "creator": "J. Ramos",
   "so": "SO-26-0451, SO-26-0452, SO-26-0453",
   "attn": "PA Fuel 118",
   "attnAddr": "Sigeul, General Santos City",
   "to": "Calumpang Fuel Station",
   "addr": "General Santos City",
   "depot": "General Santos City",
   "thru": "",
   "dest": "General Santos City",
   "plate": "MAB 4721",
   "mode": "default",
   "sub": "",
   "lines": [
    {
     "p": "DIESEL",
     "l": 8000
    },
    {
     "p": "R91",
     "l": 4000
    },
    {
     "p": "R95",
     "l": 4000
    }
   ],
   "files": [
    {
     "name": "Sales Order SO-26-0451.pdf",
     "size": "180 KB",
     "kind": "pdf"
    }
   ],
   "reason": "",
   "by": "",
   "rt": "",
   "hist": [
    {
     "t": "08:12",
     "text": "Submitted by Mindanao Fuel Haulers Inc."
    }
   ],
   "alloc": [],
   "d": null,
   "at": "08:12"
  },
  {
   "id": "ATL-2026-1052",
   "ctl": "CTL-261014-112",
   "date": "2026-10-14",
   "st": "pending",
   "sync": "synced",
   "saved": "",
   "submitted": "08:31",
   "review": "",
   "creator": "J. Ramos",
   "so": "SO-26-0454, SO-26-0455, SO-26-0456",
   "attn": "PA Fuel 118",
   "attnAddr": "Sigeul, General Santos City",
   "to": "Polomolok Agri Supply",
   "addr": "Polomolok, South Cotabato",
   "depot": "Polomolok, South Cotabato",
   "thru": "",
   "dest": "Polomolok, South Cotabato",
   "plate": "LAC 3307",
   "mode": "default",
   "sub": "",
   "lines": [
    {
     "p": "DIESEL",
     "l": 12000
    },
    {
     "p": "R91",
     "l": 8000
    },
    {
     "p": "R95",
     "l": 6000
    }
   ],
   "files": [],
   "reason": "",
   "by": "",
   "rt": "",
   "hist": [
    {
     "t": "08:31",
     "text": "Submitted by Soccsksargen Petro Logistics"
    }
   ],
   "alloc": [],
   "d": null,
   "at": "08:31"
  },
  {
   "id": "ATL-2026-1055",
   "ctl": "CTL-261014-115",
   "date": "2026-10-14",
   "st": "pending",
   "sync": "synced",
   "saved": "",
   "submitted": "10:47",
   "review": "",
   "creator": "J. Ramos",
   "so": "SO-26-0460",
   "attn": "PA Fuel 118",
   "attnAddr": "Sigeul, General Santos City",
   "to": "Alabel Cooperative Depot",
   "addr": "Alabel, Sarangani",
   "depot": "Alabel, Sarangani",
   "thru": "",
   "dest": "Alabel, Sarangani",
   "plate": "PHR 2218",
   "mode": "default",
   "sub": "",
   "lines": [
    {
     "p": "DIESEL",
     "l": 12000
    }
   ],
   "files": [
    {
     "name": "Delivery request.jpg",
     "size": "180 KB",
     "kind": "img"
    }
   ],
   "reason": "",
   "by": "",
   "rt": "",
   "hist": [
    {
     "t": "10:47",
     "text": "Submitted by Pacific Gulf Transport"
    }
   ],
   "alloc": [],
   "d": null,
   "at": "10:47"
  },
  {
   "id": "ATL-2026-1053",
   "ctl": "CTL-261014-113",
   "date": "2026-10-14",
   "st": "pending",
   "sync": "synced",
   "saved": "",
   "submitted": "10:05",
   "review": "",
   "creator": "J. Ramos",
   "so": "SO-26-0448, SO-26-0449",
   "attn": "PA Fuel 118",
   "attnAddr": "Sigeul, General Santos City",
   "to": "Glan Transport Terminal",
   "addr": "Glan, Sarangani",
   "depot": "Glan, Sarangani",
   "thru": "",
   "dest": "Glan, Sarangani",
   "plate": "NBK 6642",
   "mode": "default",
   "sub": "",
   "lines": [
    {
     "p": "DIESEL",
     "l": 6000
    },
    {
     "p": "R91",
     "l": 6000
    }
   ],
   "files": [],
   "reason": "",
   "by": "",
   "rt": "",
   "hist": [
    {
     "t": "10:05",
     "text": "Submitted by Sarangani Bay Tankers"
    }
   ],
   "alloc": [],
   "d": null,
   "at": "10:05"
  },
  {
   "id": "ATL-2026-1049",
   "ctl": "CTL-261014-109",
   "date": "2026-10-14",
   "st": "declined",
   "sync": "synced",
   "saved": "",
   "submitted": "07:22",
   "review": "",
   "creator": "J. Ramos",
   "so": "SO-26-0420, SO-26-0421",
   "attn": "PA Fuel 118",
   "attnAddr": "Sigeul, General Santos City",
   "to": "Kiamba Agri Hub",
   "addr": "Kiamba, Sarangani",
   "depot": "Kiamba, Sarangani",
   "thru": "",
   "dest": "Kiamba, Sarangani",
   "plate": "NDC 8830",
   "mode": "default",
   "sub": "",
   "lines": [
    {
     "p": "DIESEL",
     "l": 10000
    },
    {
     "p": "R91",
     "l": 10000
    }
   ],
   "files": [],
   "reason": "The Sales Order number SO-26-0421 does not match the R91 volume.",
   "by": "J. Lagumbay",
   "rt": "07:58",
   "hist": [
    {
     "t": "07:22",
     "text": "Submitted by Sarangani Bay Tankers"
    },
    {
     "t": "07:58",
     "text": "Declined by J. Lagumbay"
    }
   ],
   "alloc": [],
   "d": null,
   "at": "07:22"
  },
  {
   "id": "ATL-2026-1040",
   "ctl": "CTL-261014-100",
   "date": "2026-10-13",
   "st": "declined",
   "sync": "synced",
   "saved": "",
   "submitted": "09:15",
   "review": "",
   "creator": "J. Ramos",
   "so": "SO-26-0398",
   "attn": "PA Fuel 118",
   "attnAddr": "Sigeul, General Santos City",
   "to": "Maasim Transport Co.",
   "addr": "Maasim, Sarangani",
   "depot": "Maasim, Sarangani",
   "thru": "",
   "dest": "Maasim, Sarangani",
   "plate": "KAD 1956",
   "mode": "default",
   "sub": "",
   "lines": [
    {
     "p": "DIESEL",
     "l": 18000
    }
   ],
   "files": [],
   "reason": "The volume is above the allocation approved for this consignee.",
   "by": "M. Reyes",
   "rt": "Yesterday",
   "hist": [
    {
     "t": "Yesterday",
     "text": "Submitted by Mindanao Fuel Haulers Inc."
    },
    {
     "t": "Yesterday",
     "text": "Declined by M. Reyes"
    }
   ],
   "alloc": [],
   "d": null,
   "at": "09:15"
  },
  {
   "id": "ATL-2026-1041",
   "ctl": "CTL-261014-101",
   "date": "2026-10-14",
   "st": "approved",
   "sync": "synced",
   "saved": "",
   "submitted": "06:50",
   "review": "",
   "creator": "J. Ramos",
   "so": "SO-26-0411, SO-26-0412, SO-26-0413",
   "attn": "PA Fuel 118",
   "attnAddr": "Sigeul, General Santos City",
   "to": "Banga Fuel Depot",
   "addr": "Banga, South Cotabato",
   "depot": "Banga, South Cotabato",
   "thru": "",
   "dest": "Banga, South Cotabato",
   "plate": "NDC 8830",
   "mode": "default",
   "sub": "",
   "lines": [
    {
     "p": "DIESEL",
     "l": 10000
    },
    {
     "p": "R91",
     "l": 5000
    },
    {
     "p": "R95",
     "l": 5000
    }
   ],
   "files": [
    {
     "name": "Sales Order SO-26-0411.pdf",
     "size": "180 KB",
     "kind": "pdf"
    }
   ],
   "reason": "",
   "by": "",
   "rt": "",
   "hist": [
    {
     "t": "06:50",
     "text": "Submitted by Sarangani Bay Tankers"
    },
    {
     "t": "07:35",
     "text": "Approved by A. Dizon"
    }
   ],
   "alloc": [],
   "d": null,
   "at": "06:50"
  },
  {
   "id": "ATL-2026-1050",
   "ctl": "CTL-261014-110",
   "date": "2026-10-14",
   "st": "approved",
   "sync": "synced",
   "saved": "",
   "submitted": "07:15",
   "review": "",
   "creator": "J. Ramos",
   "so": "SO-26-0457, SO-26-0458",
   "attn": "PA Fuel 118",
   "attnAddr": "Sigeul, General Santos City",
   "to": "Alabel Cooperative Depot",
   "addr": "Alabel, Sarangani",
   "depot": "Alabel, Sarangani",
   "thru": "",
   "dest": "Alabel, Sarangani",
   "plate": "NBK 6642",
   "mode": "default",
   "sub": "",
   "lines": [
    {
     "p": "DIESEL",
     "l": 6000
    },
    {
     "p": "R91",
     "l": 3000
    }
   ],
   "files": [],
   "reason": "",
   "by": "",
   "rt": "",
   "hist": [
    {
     "t": "07:15",
     "text": "Submitted by Sarangani Bay Tankers"
    },
    {
     "t": "08:02",
     "text": "Approved by M. Reyes"
    }
   ],
   "alloc": [],
   "d": null,
   "at": "07:15"
  },
  {
   "id": "ATL-2026-1046",
   "ctl": "CTL-261014-106",
   "date": "2026-10-14",
   "st": "approved",
   "sync": "synced",
   "saved": "",
   "submitted": "06:10",
   "review": "",
   "creator": "J. Ramos",
   "so": "SO-26-0409, SO-26-0410",
   "attn": "PA Fuel 118",
   "attnAddr": "Sigeul, General Santos City",
   "to": "Banga Fuel Depot",
   "addr": "Banga, South Cotabato",
   "depot": "Banga, South Cotabato",
   "thru": "",
   "dest": "Banga, South Cotabato",
   "plate": "MAB 4721",
   "mode": "default",
   "sub": "",
   "lines": [
    {
     "p": "R95",
     "l": 4000
    },
    {
     "p": "R91",
     "l": 4000
    }
   ],
   "files": [],
   "reason": "",
   "by": "",
   "rt": "",
   "hist": [
    {
     "t": "06:10",
     "text": "Submitted by Mindanao Fuel Haulers Inc."
    },
    {
     "t": "06:50",
     "text": "Approved by M. Reyes"
    },
    {
     "t": "07:05",
     "text": "Compartments allocated by M. Reyes"
    }
   ],
   "alloc": [
    {
     "c": 1,
     "p": "R95",
     "l": 4000,
     "rem": "Standard load",
     "note": ""
    },
    {
     "c": 2,
     "p": "R91",
     "l": 4000,
     "rem": "Standard load",
     "note": ""
    }
   ],
   "d": {
    "lt": {
     "no": "LT-2026-0341",
     "st": "awaiting",
     "n": 0,
     "at": ""
    },
    "ret": false,
    "dr": {
     "no": "DR-2026-0341",
     "st": "awaiting",
     "n": 0,
     "at": ""
    },
    "wc": {
     "no": "WC-2026-0341",
     "st": "awaiting",
     "n": 0,
     "at": ""
    },
    "ho": null,
    "log": []
   },
   "at": "06:10"
  },
  {
   "id": "ATL-2026-1042",
   "ctl": "CTL-261014-102",
   "date": "2026-10-14",
   "st": "approved",
   "sync": "synced",
   "saved": "",
   "submitted": "07:05",
   "review": "",
   "creator": "J. Ramos",
   "so": "SO-26-0414, SO-26-0415",
   "attn": "PA Fuel 118",
   "attnAddr": "Sigeul, General Santos City",
   "to": "Calumpang Fuel Station",
   "addr": "General Santos City",
   "depot": "General Santos City",
   "thru": "",
   "dest": "General Santos City",
   "plate": "MAB 4721",
   "mode": "default",
   "sub": "",
   "lines": [
    {
     "p": "DIESEL",
     "l": 12000
    },
    {
     "p": "R91",
     "l": 8000
    }
   ],
   "files": [],
   "reason": "",
   "by": "",
   "rt": "",
   "hist": [
    {
     "t": "07:05",
     "text": "Submitted by Mindanao Fuel Haulers Inc."
    },
    {
     "t": "07:44",
     "text": "Approved by M. Reyes"
    },
    {
     "t": "07:50",
     "text": "Compartments allocated by M. Reyes"
    }
   ],
   "alloc": [
    {
     "c": 1,
     "p": "DIESEL",
     "l": 4000,
     "rem": "Standard load",
     "note": ""
    },
    {
     "c": 2,
     "p": "DIESEL",
     "l": 4000,
     "rem": "Standard load",
     "note": ""
    },
    {
     "c": 3,
     "p": "DIESEL",
     "l": 4000,
     "rem": "Standard load",
     "note": ""
    },
    {
     "c": 4,
     "p": "R91",
     "l": 4000,
     "rem": "Standard load",
     "note": ""
    },
    {
     "c": 5,
     "p": "R91",
     "l": 4000,
     "rem": "Standard load",
     "note": ""
    }
   ],
   "d": {
    "lt": {
     "no": "LT-2026-0342",
     "st": "printed",
     "n": 1,
     "at": "07:58"
    },
    "ret": false,
    "dr": {
     "no": "DR-2026-0342",
     "st": "awaiting",
     "n": 0,
     "at": ""
    },
    "wc": {
     "no": "WC-2026-0342",
     "st": "awaiting",
     "n": 0,
     "at": ""
    },
    "ho": null,
    "log": [
     {
      "t": "07:58",
      "text": "LT printed by M. Reyes"
     }
    ]
   },
   "at": "07:05"
  },
  {
   "id": "ATL-2026-1043",
   "ctl": "CTL-261014-103",
   "date": "2026-10-14",
   "st": "approved",
   "sync": "synced",
   "saved": "",
   "submitted": "06:20",
   "review": "",
   "creator": "J. Ramos",
   "so": "SO-26-0416, SO-26-0417",
   "attn": "PA Fuel 118",
   "attnAddr": "Sigeul, General Santos City",
   "to": "Tupi Farm Supply",
   "addr": "Tupi, South Cotabato",
   "depot": "Tupi, South Cotabato",
   "thru": "",
   "dest": "Tupi, South Cotabato",
   "plate": "KAD 1956",
   "mode": "default",
   "sub": "",
   "lines": [
    {
     "p": "DIESEL",
     "l": 12000
    },
    {
     "p": "R91",
     "l": 6000
    }
   ],
   "files": [],
   "reason": "",
   "by": "",
   "rt": "",
   "hist": [
    {
     "t": "06:20",
     "text": "Submitted by Mindanao Fuel Haulers Inc."
    },
    {
     "t": "06:58",
     "text": "Approved by M. Reyes"
    },
    {
     "t": "07:03",
     "text": "Compartments allocated by M. Reyes"
    }
   ],
   "alloc": [
    {
     "c": 1,
     "p": "DIESEL",
     "l": 6000,
     "rem": "Standard load",
     "note": ""
    },
    {
     "c": 2,
     "p": "DIESEL",
     "l": 6000,
     "rem": "Standard load",
     "note": ""
    },
    {
     "c": 3,
     "p": "R91",
     "l": 6000,
     "rem": "Standard load",
     "note": ""
    }
   ],
   "d": {
    "lt": {
     "no": "LT-2026-0343",
     "st": "printed",
     "n": 1,
     "at": "07:10"
    },
    "ret": true,
    "dr": {
     "no": "DR-2026-0343",
     "st": "awaiting",
     "n": 0,
     "at": ""
    },
    "wc": {
     "no": "WC-2026-0343",
     "st": "awaiting",
     "n": 0,
     "at": ""
    },
    "ho": null,
    "log": [
     {
      "t": "07:10",
      "text": "LT printed by M. Reyes"
     },
     {
      "t": "09:32",
      "text": "Driver returned to Operations"
     }
    ]
   },
   "at": "06:20"
  },
  {
   "id": "ATL-2026-1044",
   "ctl": "CTL-261014-104",
   "date": "2026-10-14",
   "st": "approved",
   "sync": "synced",
   "saved": "",
   "submitted": "06:35",
   "review": "",
   "creator": "J. Ramos",
   "so": "SO-26-0418, SO-26-0419, SO-26-0420",
   "attn": "PA Fuel 118",
   "attnAddr": "Sigeul, General Santos City",
   "to": "Polomolok Agri Supply",
   "addr": "Polomolok, South Cotabato",
   "depot": "Polomolok, South Cotabato",
   "thru": "",
   "dest": "Polomolok, South Cotabato",
   "plate": "LAC 3307",
   "mode": "default",
   "sub": "",
   "lines": [
    {
     "p": "DIESEL",
     "l": 8000
    },
    {
     "p": "R91",
     "l": 8000
    },
    {
     "p": "R95",
     "l": 8000
    }
   ],
   "files": [],
   "reason": "",
   "by": "",
   "rt": "",
   "hist": [
    {
     "t": "06:35",
     "text": "Submitted by Soccsksargen Petro Logistics"
    },
    {
     "t": "07:12",
     "text": "Approved by A. Dizon"
    },
    {
     "t": "07:18",
     "text": "Compartments allocated by A. Dizon"
    }
   ],
   "alloc": [
    {
     "c": 1,
     "p": "DIESEL",
     "l": 4000,
     "rem": "Standard load",
     "note": ""
    },
    {
     "c": 2,
     "p": "DIESEL",
     "l": 4000,
     "rem": "Standard load",
     "note": ""
    },
    {
     "c": 3,
     "p": "R91",
     "l": 4000,
     "rem": "Standard load",
     "note": ""
    },
    {
     "c": 4,
     "p": "R91",
     "l": 4000,
     "rem": "Standard load",
     "note": ""
    },
    {
     "c": 5,
     "p": "R95",
     "l": 4000,
     "rem": "Standard load",
     "note": ""
    },
    {
     "c": 6,
     "p": "R95",
     "l": 4000,
     "rem": "Standard load",
     "note": ""
    }
   ],
   "d": {
    "lt": {
     "no": "LT-2026-0344",
     "st": "printed",
     "n": 1,
     "at": "07:25"
    },
    "ret": true,
    "dr": {
     "no": "DR-2026-0344",
     "st": "printed",
     "n": 1,
     "at": "09:44"
    },
    "wc": {
     "no": "WC-2026-0344",
     "st": "printed",
     "n": 1,
     "at": "09:44"
    },
    "ho": null,
    "log": [
     {
      "t": "07:25",
      "text": "LT printed by A. Dizon"
     },
     {
      "t": "09:40",
      "text": "Driver returned to Operations"
     },
     {
      "t": "09:44",
      "text": "DR printed by A. Dizon"
     },
     {
      "t": "09:44",
      "text": "WC printed by A. Dizon"
     }
    ]
   },
   "at": "06:35"
  },
  {
   "id": "ATL-2026-1045",
   "ctl": "CTL-261014-105",
   "date": "2026-10-14",
   "st": "approved",
   "sync": "synced",
   "saved": "",
   "submitted": "05:55",
   "review": "",
   "creator": "J. Ramos",
   "so": "SO-26-0421, SO-26-0422",
   "attn": "PA Fuel 118",
   "attnAddr": "Sigeul, General Santos City",
   "to": "Glan Transport Terminal",
   "addr": "Glan, Sarangani",
   "depot": "Glan, Sarangani",
   "thru": "",
   "dest": "Glan, Sarangani",
   "plate": "NBK 6642",
   "mode": "default",
   "sub": "",
   "lines": [
    {
     "p": "DIESEL",
     "l": 6000
    },
    {
     "p": "R91",
     "l": 6000
    }
   ],
   "files": [],
   "reason": "",
   "by": "",
   "rt": "",
   "hist": [
    {
     "t": "05:55",
     "text": "Submitted by Sarangani Bay Tankers"
    },
    {
     "t": "06:30",
     "text": "Approved by M. Reyes"
    },
    {
     "t": "06:36",
     "text": "Compartments allocated by M. Reyes"
    }
   ],
   "alloc": [
    {
     "c": 1,
     "p": "DIESEL",
     "l": 3000,
     "rem": "Standard load",
     "note": ""
    },
    {
     "c": 2,
     "p": "DIESEL",
     "l": 3000,
     "rem": "Standard load",
     "note": ""
    },
    {
     "c": 3,
     "p": "R91",
     "l": 3000,
     "rem": "Standard load",
     "note": ""
    },
    {
     "c": 4,
     "p": "R91",
     "l": 3000,
     "rem": "Standard load",
     "note": ""
    }
   ],
   "d": {
    "lt": {
     "no": "LT-2026-0345",
     "st": "printed",
     "n": 1,
     "at": "06:42"
    },
    "ret": true,
    "dr": {
     "no": "DR-2026-0345",
     "st": "printed",
     "n": 1,
     "at": "08:15"
    },
    "wc": {
     "no": "WC-2026-0345",
     "st": "printed",
     "n": 1,
     "at": "08:15"
    },
    "ho": {
     "staff": "M. Reyes",
     "time": "08:21"
    },
    "log": [
     {
      "t": "06:42",
      "text": "LT printed by M. Reyes"
     },
     {
      "t": "08:10",
      "text": "Driver returned to Operations"
     },
     {
      "t": "08:15",
      "text": "DR printed by M. Reyes"
     },
     {
      "t": "08:15",
      "text": "WC printed by M. Reyes"
     },
     {
      "t": "08:21",
      "text": "DR and WC handed over to Dennis Villareal. Released by M. Reyes"
     }
    ]
   },
   "at": "05:55"
  },
  {
   "id": "ATL-2026-1063",
   "ctl": "CTL-261014-011",
   "date": "2026-10-14",
   "st": "draft",
   "sync": "synced",
   "saved": "09:48",
   "submitted": "",
   "review": "",
   "creator": "A. Mendoza",
   "so": "",
   "attn": "PA Fuel 118",
   "attnAddr": "Sigeul, General Santos City",
   "to": "Conel Agri Trading",
   "addr": "Km 12, National Highway, Brgy. Conel, General Santos City",
   "depot": "Conel, General Santos City",
   "thru": "",
   "dest": "Conel, General Santos City",
   "plate": "NBK 6642",
   "mode": "default",
   "sub": "",
   "lines": [
    {
     "p": "DIESEL",
     "l": 9000
    }
   ],
   "files": [],
   "reason": "",
   "by": "",
   "rt": "",
   "hist": [],
   "alloc": [],
   "d": null,
   "at": "09:48"
  },
  {
   "id": "ATL-2026-1064",
   "ctl": "CTL-261014-012",
   "date": "2026-10-14",
   "st": "draft",
   "sync": "local",
   "saved": "10:32",
   "submitted": "",
   "review": "",
   "creator": "A. Mendoza",
   "so": "",
   "attn": "PA Fuel 118",
   "attnAddr": "Sigeul, General Santos City",
   "to": "Tupi Fuel and Lubes",
   "addr": "Zone 2, Poblacion, Tupi, South Cotabato",
   "depot": "Tupi, South Cotabato",
   "thru": "Estrada Haulage",
   "dest": "Tupi, South Cotabato",
   "plate": "",
   "mode": "default",
   "sub": "",
   "lines": [
    {
     "p": "",
     "l": ""
    }
   ],
   "files": [],
   "reason": "",
   "by": "",
   "rt": "",
   "hist": [],
   "alloc": [],
   "d": null,
   "at": "10:32"
  },
  {
   "id": "ATL-2026-1065",
   "ctl": "CTL-261014-013",
   "date": "2026-10-14",
   "st": "pending",
   "sync": "queued",
   "saved": "11:05",
   "submitted": "",
   "review": "",
   "creator": "A. Mendoza",
   "so": "SO-26-0447",
   "attn": "PA Fuel 118",
   "attnAddr": "Sigeul, General Santos City",
   "to": "Lagao Commercial Fuels",
   "addr": "Brgy. Lagao, General Santos City",
   "depot": "Lagao, General Santos City",
   "thru": "",
   "dest": "Lagao, General Santos City",
   "plate": "MAB 4721",
   "mode": "default",
   "sub": "",
   "lines": [
    {
     "p": "DIESEL",
     "l": 16000
    }
   ],
   "files": [
    {
     "name": "SO-26-0447.pdf",
     "size": "212 KB",
     "kind": "pdf"
    }
   ],
   "reason": "",
   "by": "",
   "rt": "",
   "hist": [
    {
     "t": "11:05",
     "text": "Submitted offline. Waiting to sync."
    }
   ],
   "alloc": [],
   "d": null,
   "at": "11:05"
  },
  {
   "id": "ATL-2026-1060",
   "ctl": "CTL-261014-008",
   "date": "2026-10-14",
   "st": "pending",
   "sync": "synced",
   "saved": "",
   "submitted": "08:40",
   "review": "inreview",
   "creator": "A. Mendoza",
   "so": "SO-26-0436, SO-26-0437",
   "attn": "PA Fuel 118",
   "attnAddr": "Sigeul, General Santos City",
   "to": "Apopong Fuel Station",
   "addr": "Purok 7, Brgy. Apopong, General Santos City",
   "depot": "Apopong, General Santos City",
   "thru": "Tolentino Haulage",
   "dest": "Apopong, General Santos City",
   "plate": "KAD 1956",
   "mode": "sub",
   "sub": "D1",
   "lines": [
    {
     "p": "R95",
     "l": 6000
    },
    {
     "p": "DIESEL",
     "l": 12000
    }
   ],
   "files": [
    {
     "name": "SO-26-0436 and 0437.pdf",
     "size": "348 KB",
     "kind": "pdf"
    },
    {
     "name": "Authorization letter, R. Baculi.pdf",
     "size": "96 KB",
     "kind": "pdf"
    }
   ],
   "reason": "",
   "by": "",
   "rt": "",
   "hist": [
    {
     "t": "08:40",
     "text": "Submitted"
    },
    {
     "t": "09:05",
     "text": "Operations started review"
    }
   ],
   "alloc": [],
   "d": null,
   "at": "08:40"
  },
  {
   "id": "ATL-2026-1061",
   "ctl": "CTL-261014-009",
   "date": "2026-10-14",
   "st": "pending",
   "sync": "synced",
   "saved": "",
   "submitted": "09:22",
   "review": "queue",
   "creator": "A. Mendoza",
   "so": "SO-26-0441, SO-26-0442",
   "attn": "PA Fuel 118",
   "attnAddr": "Sigeul, General Santos City",
   "to": "Polomolok Agri Supply",
   "addr": "Brgy. Mabuhay, Polomolok, South Cotabato",
   "depot": "Polomolok, South Cotabato",
   "thru": "",
   "dest": "Polomolok, South Cotabato",
   "plate": "LAC 3307",
   "mode": "default",
   "sub": "",
   "lines": [
    {
     "p": "R91",
     "l": 8000
    },
    {
     "p": "R95",
     "l": 4000
    }
   ],
   "files": [
    {
     "name": "SO-26-0441 and 0442.pdf",
     "size": "301 KB",
     "kind": "pdf"
    }
   ],
   "reason": "",
   "by": "",
   "rt": "",
   "hist": [
    {
     "t": "09:22",
     "text": "Submitted"
    }
   ],
   "alloc": [],
   "d": null,
   "at": "09:22"
  },
  {
   "id": "ATL-2026-1056",
   "ctl": "CTL-261014-004",
   "date": "2026-10-14",
   "st": "approved",
   "sync": "synced",
   "saved": "",
   "submitted": "06:20",
   "review": "",
   "creator": "A. Mendoza",
   "so": "SO-26-0401, SO-26-0402, SO-26-0403",
   "attn": "PA Fuel 118",
   "attnAddr": "Sigeul, General Santos City",
   "to": "Calumpang Fuel Station",
   "addr": "Brgy. Calumpang, General Santos City",
   "depot": "Calumpang, General Santos City",
   "thru": "",
   "dest": "Calumpang, General Santos City",
   "plate": "MAB 4721",
   "mode": "default",
   "sub": "",
   "lines": [
    {
     "p": "R95",
     "l": 4000
    },
    {
     "p": "R91",
     "l": 8000
    },
    {
     "p": "DIESEL",
     "l": 8000
    }
   ],
   "files": [
    {
     "name": "SO-26-0401 to 0403.pdf",
     "size": "402 KB",
     "kind": "pdf"
    }
   ],
   "reason": "",
   "by": "",
   "rt": "",
   "hist": [
    {
     "t": "06:20",
     "text": "Submitted"
    },
    {
     "t": "07:02",
     "text": "Approved by Operations"
    }
   ],
   "alloc": [
    {
     "c": 1,
     "p": "R95",
     "l": 4000,
     "rem": "Standard load",
     "note": ""
    },
    {
     "c": 2,
     "p": "R91",
     "l": 4000,
     "rem": "Standard load",
     "note": ""
    },
    {
     "c": 3,
     "p": "R91",
     "l": 4000,
     "rem": "Standard load",
     "note": ""
    },
    {
     "c": 4,
     "p": "DIESEL",
     "l": 4000,
     "rem": "Standard load",
     "note": ""
    },
    {
     "c": 5,
     "p": "DIESEL",
     "l": 4000,
     "rem": "Standard load",
     "note": ""
    }
   ],
   "d": {
    "lt": {
     "no": "LT-2026-0347",
     "st": "printed",
     "n": 1,
     "at": "07:10"
    },
    "ret": false,
    "dr": {
     "no": "DR-2026-0347",
     "st": "awaiting",
     "n": 0,
     "at": ""
    },
    "wc": {
     "no": "WC-2026-0347",
     "st": "awaiting",
     "n": 0,
     "at": ""
    },
    "ho": null,
    "log": [
     {
      "t": "07:10",
      "text": "LT printed by M. Reyes"
     }
    ]
   },
   "at": "06:20"
  },
  {
   "id": "ATL-2026-1057",
   "ctl": "CTL-261013-021",
   "date": "2026-10-13",
   "st": "approved",
   "sync": "synced",
   "saved": "",
   "submitted": "14:10",
   "review": "",
   "creator": "A. Mendoza",
   "so": "SO-26-0388, SO-26-0389",
   "attn": "PA Fuel 118",
   "attnAddr": "Sigeul, General Santos City",
   "to": "Lagao Commercial Fuels",
   "addr": "Purok 4, Brgy. Lagao, General Santos City",
   "depot": "Lagao, General Santos City",
   "thru": "Santos Haulage",
   "dest": "Lagao, General Santos City",
   "plate": "LAC 3307",
   "mode": "default",
   "sub": "",
   "lines": [
    {
     "p": "DIESEL",
     "l": 12000
    },
    {
     "p": "R91",
     "l": 12000
    }
   ],
   "files": [
    {
     "name": "SO-26-0388 and 0389.pdf",
     "size": "377 KB",
     "kind": "pdf"
    }
   ],
   "reason": "",
   "by": "",
   "rt": "",
   "hist": [
    {
     "t": "14:10",
     "text": "Submitted"
    },
    {
     "t": "14:48",
     "text": "Approved by Operations"
    },
    {
     "t": "16:30",
     "text": "DR and WC released to the driver"
    }
   ],
   "alloc": [
    {
     "c": 1,
     "p": "DIESEL",
     "l": 4000,
     "rem": "Standard load",
     "note": ""
    },
    {
     "c": 2,
     "p": "DIESEL",
     "l": 4000,
     "rem": "Standard load",
     "note": ""
    },
    {
     "c": 3,
     "p": "DIESEL",
     "l": 4000,
     "rem": "Standard load",
     "note": ""
    },
    {
     "c": 4,
     "p": "R91",
     "l": 4000,
     "rem": "Standard load",
     "note": ""
    },
    {
     "c": 5,
     "p": "R91",
     "l": 4000,
     "rem": "Standard load",
     "note": ""
    },
    {
     "c": 6,
     "p": "R91",
     "l": 4000,
     "rem": "Standard load",
     "note": ""
    }
   ],
   "d": {
    "lt": {
     "no": "LT-2026-0338",
     "st": "printed",
     "n": 1,
     "at": "15:10"
    },
    "ret": true,
    "dr": {
     "no": "DR-2026-0338",
     "st": "printed",
     "n": 1,
     "at": "16:20"
    },
    "wc": {
     "no": "WC-2026-0338",
     "st": "printed",
     "n": 1,
     "at": "16:20"
    },
    "ho": {
     "staff": "M. Reyes",
     "time": "16:30"
    },
    "log": [
     {
      "t": "15:10",
      "text": "LT printed by M. Reyes"
     },
     {
      "t": "16:15",
      "text": "Driver returned to Operations"
     },
     {
      "t": "16:20",
      "text": "DR printed by M. Reyes"
     },
     {
      "t": "16:20",
      "text": "WC printed by M. Reyes"
     },
     {
      "t": "16:30",
      "text": "DR and WC handed over to Michael Tuazon. Released by M. Reyes"
     }
    ]
   },
   "at": "14:10"
  },
  {
   "id": "ATL-2026-1058",
   "ctl": "CTL-261014-006",
   "date": "2026-10-14",
   "st": "approved",
   "sync": "synced",
   "saved": "",
   "submitted": "07:35",
   "review": "",
   "creator": "A. Mendoza",
   "so": "SO-26-0415, SO-26-0416",
   "attn": "PA Fuel 118",
   "attnAddr": "Sigeul, General Santos City",
   "to": "Glan Transport Terminal",
   "addr": "Brgy. Glan Padidu, Glan, Sarangani",
   "depot": "Glan, Sarangani",
   "thru": "",
   "dest": "Glan, Sarangani",
   "plate": "NBK 6642",
   "mode": "default",
   "sub": "",
   "lines": [
    {
     "p": "R91",
     "l": 6000
    },
    {
     "p": "DIESEL",
     "l": 6000
    }
   ],
   "files": [
    {
     "name": "SO-26-0415 and 0416.pdf",
     "size": "290 KB",
     "kind": "pdf"
    }
   ],
   "reason": "",
   "by": "",
   "rt": "",
   "hist": [
    {
     "t": "07:35",
     "text": "Submitted"
    },
    {
     "t": "08:15",
     "text": "Approved by Operations"
    }
   ],
   "alloc": [],
   "d": null,
   "at": "07:35"
  },
  {
   "id": "ATL-2026-1059",
   "ctl": "CTL-261014-003",
   "date": "2026-10-14",
   "st": "declined",
   "sync": "synced",
   "saved": "",
   "submitted": "06:05",
   "review": "",
   "creator": "A. Mendoza",
   "so": "SO-26-0431, SO-26-0432",
   "attn": "PA Fuel 118",
   "attnAddr": "Sigeul, General Santos City",
   "to": "Koronadal Fuel Hub",
   "addr": "Brgy. Lapu, Koronadal City",
   "depot": "Koronadal City, South Cotabato",
   "thru": "",
   "dest": "Koronadal City, South Cotabato",
   "plate": "NDC 8830",
   "mode": "default",
   "sub": "",
   "lines": [
    {
     "p": "R91",
     "l": 10000
    },
    {
     "p": "DIESEL",
     "l": 10000
    }
   ],
   "files": [
    {
     "name": "SO-26-0431 and 0432.pdf",
     "size": "318 KB",
     "kind": "pdf"
    }
   ],
   "reason": "The Sales Order number SO-26-0432 does not match the requested Diesel volume of 10,000 L.",
   "by": "J. Lagumbay",
   "rt": "11:42",
   "hist": [
    {
     "t": "06:05",
     "text": "Submitted"
    },
    {
     "t": "11:42",
     "text": "Declined by Operations (J. Lagumbay)"
    }
   ],
   "alloc": [],
   "d": null,
   "at": "06:05"
  },
  {
   "id": "ATL-2026-1054",
   "ctl": "CTL-261013-017",
   "date": "2026-10-13",
   "st": "declined",
   "sync": "synced",
   "saved": "",
   "submitted": "10:30",
   "review": "",
   "creator": "A. Mendoza",
   "so": "SO-26-0412",
   "attn": "PA Fuel 118",
   "attnAddr": "Sigeul, General Santos City",
   "to": "Lagao Commercial Fuels",
   "addr": "Purok 4, Brgy. Lagao, General Santos City",
   "depot": "Lagao, General Santos City",
   "thru": "Santos Haulage",
   "dest": "Lagao, General Santos City",
   "plate": "KAD 1956",
   "mode": "default",
   "sub": "",
   "lines": [
    {
     "p": "DIESEL",
     "l": 18000
    }
   ],
   "files": [
    {
     "name": "SO-26-0412.pdf",
     "size": "188 KB",
     "kind": "pdf"
    }
   ],
   "reason": "The requested volume is above the allocation approved for this customer.",
   "by": "M. Reyes",
   "rt": "15:10",
   "hist": [
    {
     "t": "10:30",
     "text": "Submitted"
    },
    {
     "t": "15:10",
     "text": "Declined by Operations (M. Reyes)"
    }
   ],
   "alloc": [],
   "d": null,
   "at": "10:30"
  }
 ],
 "act": [
  {
   "t": "10:47",
   "k": "info",
   "x": "ATL-2026-1055 submitted by Pacific Gulf Transport"
  },
  {
   "t": "10:05",
   "k": "info",
   "x": "ATL-2026-1053 submitted by Sarangani Bay Tankers"
  },
  {
   "t": "09:44",
   "k": "ok",
   "x": "DR and WC printed for ATL-2026-1044"
  },
  {
   "t": "09:32",
   "k": "info",
   "x": "Driver Arnel Pascua returned to Operations (ATL-2026-1043)"
  },
  {
   "t": "08:31",
   "k": "info",
   "x": "ATL-2026-1052 submitted by Soccsksargen Petro Logistics"
  },
  {
   "t": "08:02",
   "k": "ok",
   "x": "ATL-2026-1050 approved. Ready for compartment allocation"
  },
  {
   "t": "07:58",
   "k": "bad",
   "x": "ATL-2026-1049 declined: Sales Order number mismatch"
  }
 ],
 "notes": [
  {
   "t": "11:42",
   "k": "bad",
   "x": "ATL-2026-1059 declined: Sales Order SO-26-0432 does not match the requested Diesel volume.",
   "id": "ATL-2026-1059",
   "u": true
  },
  {
   "t": "09:05",
   "k": "info",
   "x": "ATL-2026-1060 is now in review with Operations.",
   "id": "ATL-2026-1060",
   "u": true
  },
  {
   "t": "08:15",
   "k": "ok",
   "x": "ATL-2026-1058 approved. Operations will allocate the compartments next.",
   "id": "ATL-2026-1058",
   "u": false
  },
  {
   "t": "07:02",
   "k": "ok",
   "x": "ATL-2026-1056 approved. Loading Ticket printed.",
   "id": "ATL-2026-1056",
   "u": false
  },
  {
   "t": "Yest.",
   "k": "ok",
   "x": "DR and WC for ATL-2026-1057 released to the driver.",
   "id": "ATL-2026-1057",
   "u": false
  },
  {
   "t": "Yest.",
   "k": "bad",
   "x": "ATL-2026-1054 declined. Open it to read the reason.",
   "id": "ATL-2026-1054",
   "u": false
  }
 ],
 "atlUser": "A. Mendoza"
}
JSON;
    }
}
