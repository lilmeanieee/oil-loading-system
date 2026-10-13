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
    public const VERSION = 1;

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
 "ver": 1,
 "staff": [
  "M. Reyes",
  "J. Lagumbay",
  "A. Dizon",
  "R. Cabahug"
 ],
 "seq": {
  "lt": 345,
  "dr": 345,
  "wc": 345
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
   "saf": "2027-01-09"
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
   "saf": "2027-02-20"
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
   "saf": "2027-03-15"
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
   "saf": "2027-06-30"
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
   "saf": "2026-12-31"
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
   "saf": "2027-04-23"
  }
 ],
 "atl": [
  {
   "id": "ATL-2026-1051",
   "st": "pending",
   "at": "08:12",
   "to": "Calumpang Fuel Station, General Santos City",
   "truck": "MAB 4721",
   "driver": "D1",
   "h": "H1",
   "lines": [
    {
     "c": 1,
     "p": "DIESEL",
     "l": 4000
    },
    {
     "c": 2,
     "p": "DIESEL",
     "l": 4000
    },
    {
     "c": 3,
     "p": "R91",
     "l": 4000
    },
    {
     "c": 4,
     "p": "R95",
     "l": 4000
    },
    {
     "c": 5,
     "p": "DIESEL",
     "l": 3000
    }
   ],
   "reason": "",
   "files": [
    "Purchase order.pdf",
    "Truck calibration certificate.pdf",
    "Driver licence.jpg"
   ],
   "hist": [
    {
     "t": "08:12",
     "text": "Submitted by Mindanao Fuel Haulers Inc."
    }
   ],
   "d": null
  },
  {
   "id": "ATL-2026-1052",
   "st": "pending",
   "at": "08:31",
   "to": "Polomolok Agri Supply, Polomolok, South Cotabato",
   "truck": "LAC 3307",
   "driver": "D4",
   "h": "H3",
   "lines": [
    {
     "c": 1,
     "p": "DIESEL",
     "l": 4000
    },
    {
     "c": 2,
     "p": "DIESEL",
     "l": 4500
    },
    {
     "c": 3,
     "p": "DIESEL",
     "l": 4000
    },
    {
     "c": 4,
     "p": "R91",
     "l": 4000
    },
    {
     "c": 5,
     "p": "R91",
     "l": 4000
    },
    {
     "c": 6,
     "p": "R95",
     "l": 2000
    }
   ],
   "reason": "",
   "files": [
    "Purchase order.pdf",
    "Truck calibration certificate.pdf",
    "Driver licence.jpg"
   ],
   "hist": [
    {
     "t": "08:31",
     "text": "Submitted by Soccsksargen Petro Logistics"
    }
   ],
   "d": null
  },
  {
   "id": "ATL-2026-1055",
   "st": "pending",
   "at": "10:47",
   "to": "Alabel Cooperative Depot, Alabel, Sarangani",
   "truck": "PHR 2218",
   "driver": "D6",
   "h": "H4",
   "lines": [
    {
     "c": 1,
     "p": "DIESEL",
     "l": 4000
    },
    {
     "c": 2,
     "p": "DIESEL",
     "l": 4000
    },
    {
     "c": 3,
     "p": "DIESEL",
     "l": 4000
    }
   ],
   "reason": "",
   "files": [
    "Purchase order.pdf",
    "Truck calibration certificate.pdf",
    "Driver licence.jpg"
   ],
   "hist": [
    {
     "t": "10:47",
     "text": "Submitted by Pacific Gulf Transport"
    }
   ],
   "d": null
  },
  {
   "id": "ATL-2026-1053",
   "st": "resubmitted",
   "at": "10:05",
   "to": "Glan Transport Terminal, Glan, Sarangani",
   "truck": "NBK 6642",
   "driver": "D5",
   "h": "H2",
   "lines": [
    {
     "c": 1,
     "p": "DIESEL",
     "l": 3000
    },
    {
     "c": 2,
     "p": "DIESEL",
     "l": 3000
    },
    {
     "c": 3,
     "p": "R91",
     "l": 3000
    },
    {
     "c": 4,
     "p": "R91",
     "l": 3000
    }
   ],
   "reason": "Compartment 3 and 4 allocation was missing from the first submission.",
   "files": [
    "Purchase order.pdf",
    "Truck calibration certificate.pdf",
    "Driver licence.jpg"
   ],
   "hist": [
    {
     "t": "07:40",
     "text": "Submitted by Sarangani Bay Tankers"
    },
    {
     "t": "08:20",
     "text": "Returned for correction by M. Reyes"
    },
    {
     "t": "10:05",
     "text": "Resubmitted with compartment allocation filled in"
    }
   ],
   "d": null
  },
  {
   "id": "ATL-2026-1049",
   "st": "returned",
   "at": "07:22",
   "to": "Kiamba Agri Hub, Kiamba, Sarangani",
   "truck": "NDC 8830",
   "driver": "D2",
   "h": "H2",
   "lines": [
    {
     "c": 1,
     "p": "DIESEL",
     "l": 5000
    },
    {
     "c": 2,
     "p": "DIESEL",
     "l": 5000
    },
    {
     "c": 3,
     "p": "R91",
     "l": 5000
    },
    {
     "c": 4,
     "p": "R91",
     "l": 4000
    }
   ],
   "reason": "The calibration certificate attachment is unreadable. Upload a clear copy.",
   "files": [
    "Purchase order.pdf",
    "Truck calibration certificate.pdf",
    "Driver licence.jpg"
   ],
   "hist": [
    {
     "t": "07:22",
     "text": "Submitted by Sarangani Bay Tankers"
    },
    {
     "t": "07:58",
     "text": "Returned for correction by J. Lagumbay"
    }
   ],
   "d": null
  },
  {
   "id": "ATL-2026-1040",
   "st": "declined",
   "at": "Yesterday",
   "to": "Maasim Transport Co., Maasim, Sarangani",
   "truck": "KAD 1956",
   "driver": "D3",
   "h": "H1",
   "lines": [
    {
     "c": 1,
     "p": "DIESEL",
     "l": 6000
    },
    {
     "c": 2,
     "p": "DIESEL",
     "l": 6000
    },
    {
     "c": 3,
     "p": "DIESEL",
     "l": 6000
    }
   ],
   "reason": "The volume is above the allocation approved for this consignee.",
   "files": [
    "Purchase order.pdf",
    "Truck calibration certificate.pdf",
    "Driver licence.jpg"
   ],
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
   "d": null
  },
  {
   "id": "ATL-2026-1041",
   "st": "approved",
   "at": "06:50",
   "to": "Banga Fuel Depot, Banga, South Cotabato",
   "truck": "NDC 8830",
   "driver": "D2",
   "h": "H2",
   "lines": [
    {
     "c": 1,
     "p": "DIESEL",
     "l": 5000
    },
    {
     "c": 2,
     "p": "DIESEL",
     "l": 5000
    },
    {
     "c": 3,
     "p": "R91",
     "l": 5000
    },
    {
     "c": 4,
     "p": "R95",
     "l": 5000
    }
   ],
   "reason": "",
   "files": [
    "Purchase order.pdf",
    "Truck calibration certificate.pdf",
    "Driver licence.jpg"
   ],
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
   }
  },
  {
   "id": "ATL-2026-1042",
   "st": "approved",
   "at": "07:05",
   "to": "Calumpang Fuel Station, General Santos City",
   "truck": "MAB 4721",
   "driver": "D1",
   "h": "H1",
   "lines": [
    {
     "c": 1,
     "p": "DIESEL",
     "l": 4000
    },
    {
     "c": 2,
     "p": "DIESEL",
     "l": 4000
    },
    {
     "c": 3,
     "p": "DIESEL",
     "l": 4000
    },
    {
     "c": 4,
     "p": "R91",
     "l": 4000
    },
    {
     "c": 5,
     "p": "R91",
     "l": 4000
    }
   ],
   "reason": "",
   "files": [
    "Purchase order.pdf",
    "Truck calibration certificate.pdf",
    "Driver licence.jpg"
   ],
   "hist": [
    {
     "t": "07:05",
     "text": "Submitted by Mindanao Fuel Haulers Inc."
    },
    {
     "t": "07:44",
     "text": "Approved by M. Reyes"
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
   }
  },
  {
   "id": "ATL-2026-1043",
   "st": "approved",
   "at": "06:20",
   "to": "Tupi Farm Supply, Tupi, South Cotabato",
   "truck": "KAD 1956",
   "driver": "D3",
   "h": "H1",
   "lines": [
    {
     "c": 1,
     "p": "DIESEL",
     "l": 6000
    },
    {
     "c": 2,
     "p": "DIESEL",
     "l": 6000
    },
    {
     "c": 3,
     "p": "R91",
     "l": 6000
    }
   ],
   "reason": "",
   "files": [
    "Purchase order.pdf",
    "Truck calibration certificate.pdf",
    "Driver licence.jpg"
   ],
   "hist": [
    {
     "t": "06:20",
     "text": "Submitted by Mindanao Fuel Haulers Inc."
    },
    {
     "t": "06:58",
     "text": "Approved by M. Reyes"
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
   }
  },
  {
   "id": "ATL-2026-1044",
   "st": "approved",
   "at": "06:35",
   "to": "Polomolok Agri Supply, Polomolok, South Cotabato",
   "truck": "LAC 3307",
   "driver": "D4",
   "h": "H3",
   "lines": [
    {
     "c": 1,
     "p": "DIESEL",
     "l": 4000
    },
    {
     "c": 2,
     "p": "DIESEL",
     "l": 4000
    },
    {
     "c": 3,
     "p": "R91",
     "l": 4000
    },
    {
     "c": 4,
     "p": "R91",
     "l": 4000
    },
    {
     "c": 5,
     "p": "R95",
     "l": 4000
    },
    {
     "c": 6,
     "p": "DIESEL",
     "l": 2000
    }
   ],
   "reason": "",
   "files": [
    "Purchase order.pdf",
    "Truck calibration certificate.pdf",
    "Driver licence.jpg"
   ],
   "hist": [
    {
     "t": "06:35",
     "text": "Submitted by Soccsksargen Petro Logistics"
    },
    {
     "t": "07:12",
     "text": "Approved by A. Dizon"
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
   }
  },
  {
   "id": "ATL-2026-1045",
   "st": "approved",
   "at": "05:55",
   "to": "Glan Transport Terminal, Glan, Sarangani",
   "truck": "NBK 6642",
   "driver": "D5",
   "h": "H2",
   "lines": [
    {
     "c": 1,
     "p": "DIESEL",
     "l": 3000
    },
    {
     "c": 2,
     "p": "DIESEL",
     "l": 3000
    },
    {
     "c": 3,
     "p": "R91",
     "l": 3000
    },
    {
     "c": 4,
     "p": "R91",
     "l": 3000
    }
   ],
   "reason": "",
   "files": [
    "Purchase order.pdf",
    "Truck calibration certificate.pdf",
    "Driver licence.jpg"
   ],
   "hist": [
    {
     "t": "05:55",
     "text": "Submitted by Sarangani Bay Tankers"
    },
    {
     "t": "06:30",
     "text": "Approved by M. Reyes"
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
   }
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
   "x": "ATL-2026-1053 resubmitted by Sarangani Bay Tankers"
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
   "t": "08:21",
   "k": "ok",
   "x": "DR and WC for ATL-2026-1045 handed to driver and released"
  },
  {
   "t": "07:58",
   "k": "bad",
   "x": "ATL-2026-1049 returned for correction: unreadable certificate"
  }
 ]
}
JSON;
    }
}
