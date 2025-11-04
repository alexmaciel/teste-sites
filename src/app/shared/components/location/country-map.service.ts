import { Injectable } from '@angular/core';
import * as am5 from '@amcharts/amcharts5';

@Injectable({
  providedIn: 'root'
})
export class CountryMapService {

  constructor() {}

  getCountries() {
    return [
      { id: 'BR', title: 'Brasil : Escri',
        polygonSettings: { fill: am5.color(0xE66B23) },
        longitude: -47.9292, latitude: -15.7801,
        geometry: { type: 'Point', coordinates: [-47.9292, -15.7801] }
      },
      { id: 'AR', title: 'Argentina',
        polygonSettings: { fill: am5.color(0xE66B23) },
        longitude: -58.3816, latitude: -34.6037,
        geometry: { type: 'Point', coordinates: [-58.3816, -34.6037] }
      },
      { id: 'US', title: 'Estados Unidos',
        polygonSettings: { fill: am5.color(0xE66B23) },
        longitude: -77.0369, latitude: 38.9072,
        geometry: { type: 'Point', coordinates: [-77.0369, 38.9072] }
      },
      { id: 'EC', title: 'Equador',
        polygonSettings: { fill: am5.color(0xE66B23) },
        longitude: -78.4678, latitude: -0.1807,
        geometry: { type: 'Point', coordinates: [-78.4678, -0.1807] }
      },
      { id: 'IN', title: 'Índia',
        polygonSettings: { fill: am5.color(0xE66B23) },
        longitude: 77.2090, latitude: 28.6139,
        geometry: { type: 'Point', coordinates: [77.2090, 28.6139] }
      },
      { id: 'ID', title: 'Indonésia',
        polygonSettings: { fill: am5.color(0xE66B23) },
        longitude: 106.8456, latitude: -6.2088,
        geometry: { type: 'Point', coordinates: [106.8456, -6.2088] }
      },
      { id: 'UY', title: 'Uruguai',
        polygonSettings: { fill: am5.color(0xE66B23) },
        longitude: -56.1645, latitude: -34.9011,
        geometry: { type: 'Point', coordinates: [-56.1645, -34.9011] }
      },
      { id: 'CL', title: 'Chile',
        polygonSettings: { fill: am5.color(0xE66B23) },
        longitude: -70.6693, latitude: -33.4489,
        geometry: { type: 'Point', coordinates: [-70.6693, -33.4489] }
      },
      { id: 'MM', title: 'Mianmar',
        polygonSettings: { fill: am5.color(0xE66B23) },
        longitude: 96.0785, latitude: 19.7633,
        geometry: { type: 'Point', coordinates: [96.0785, 19.7633] }
      },
      { id: 'MY', title: 'Malásia',
        polygonSettings: { fill: am5.color(0xE66B23) },
        longitude: 101.6869, latitude: 3.1390,
        geometry: { type: 'Point', coordinates: [101.6869, 3.1390] }
      },
      { id: 'BD', title: 'Bangladesh',
        polygonSettings: { fill: am5.color(0xE66B23) },
        longitude: 90.4125, latitude: 23.8103,
        geometry: { type: 'Point', coordinates: [90.4125, 23.8103] }
      },
      { id: 'PH', title: 'Filipinas',
        polygonSettings: { fill: am5.color(0xE66B23) },
        longitude: 121.7740, latitude: 12.8797,
        geometry: { type: 'Point', coordinates: [121.7740, 12.8797] }
      },
      { id: 'TH', title: 'Tailândia',
        polygonSettings: { fill: am5.color(0xE66B23) },
        longitude: 100.5018, latitude: 13.7563,
        geometry: { type: 'Point', coordinates: [100.5018, 13.7563] }
      },
      { id: 'KH', title: 'Camboja',
        polygonSettings: { fill: am5.color(0xE66B23) },
        longitude: 104.9282, latitude: 11.5564,
        geometry: { type: 'Point', coordinates: [104.9282, 11.5564] }
      },
      { id: 'VN', title: 'Vietnã',
        polygonSettings: { fill: am5.color(0xE66B23) },
        longitude: 105.8342, latitude: 21.0278,
        geometry: { type: 'Point', coordinates: [105.8342, 21.0278] }
      },
      { id: 'LA', title: 'Laos',
        polygonSettings: { fill: am5.color(0xE66B23) },
        longitude: 102.6331, latitude: 17.9757,
        geometry: { type: 'Point', coordinates: [102.6331, 17.9757] }
      }
    ];
  }
}
