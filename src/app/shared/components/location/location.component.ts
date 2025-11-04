import { Component, Inject, NgZone, PLATFORM_ID, OnInit, OnDestroy } from '@angular/core';
import { Router } from '@angular/router';
import { isPlatformBrowser } from '@angular/common';

import { LocalizeRouterService } from '@gilsdav/ngx-translate-router';

// amCharts imports
import * as am5 from "@amcharts/amcharts5";
import * as am5map from "@amcharts/amcharts5/map";
import am5themes_Animated from "@amcharts/amcharts5/themes/Animated";

import am5geodata_lang_EN from '@amcharts/amcharts5-geodata/lang/EN';
import am5geodata_lang_ES from '@amcharts/amcharts5-geodata/lang/ES';
import am5geodata_lang_PT from '@amcharts/amcharts5-geodata/lang/PT';

// Geodata (mapa simplificado do mundo)
import am5geodata_worldLow from "@amcharts/amcharts5-geodata/worldLow";

import { CountryMapService } from './country-map.service';

@Component({
  selector: 'app-location',
  templateUrl: './location.component.html'
})
export class LocationComponent implements OnInit, OnDestroy {
  private root!: am5.Root;
  private chart!: am5map.MapChart;
  private polygonSeries!: am5map.MapPolygonSeries;

  private lineSeries!: am5map.MapLineSeries;
  private animatedLineSeries!: am5map.MapLineSeries;
  private animatedBulletSeries!: am5map.MapPointSeries;

  private activeLabel?: am5.Label;
  private originInterval?: any;

  private originCountryId = 'UY';
  private countryCentersSeries!: am5map.MapPointSeries;

  
  countries: any[] = [];

  constructor(
    private router: Router,
    private localize: LocalizeRouterService,    
    private countryMapService: CountryMapService,
    @Inject(PLATFORM_ID) private platformId: object, private zone: NgZone
  ) {}

  // Run the function only in the browser
  browserOnly(f: () => void) {
    if (isPlatformBrowser(this.platformId)) {
      this.zone.runOutsideAngular(() => {
        f();
      });
    }
  }

  ngOnInit() {
    this.countries = this.countryMapService.getCountries();
    const validCountries = this.countries.filter(c => c.longitude && c.latitude);
    const randomCountry = validCountries[Math.floor(Math.random() * validCountries.length)];
    this.originCountryId = randomCountry.id;

    const highlightSeries = this.countries.map(({ id, polygonSettings }) => ({ id, polygonSettings }));
      
    // Chart code goes in here
    this.browserOnly(() => {
      const currentLang = this.localize.parser.currentLang;
      let geoNames: any = am5geodata_lang_EN;
      switch (currentLang) {
        case 'es':
          geoNames = am5geodata_lang_ES;
          break;
        case 'pt':
        case 'pt-br':
        case 'pt_BR':
          geoNames = am5geodata_lang_PT;
          break;
        default:
          geoNames = am5geodata_lang_EN;
      }

      const root = am5.Root.new("chartdiv");
      this.root = root;

      root.setThemes([am5themes_Animated.new(root)]);

      // Criar o mapa
      this.chart = root.container.children.push(
        am5map.MapChart.new(root, {
          panX: "translateX",
          panY: "translateY",
          wheelX: "none",
          wheelY: "none",
          pinchZoom: false,
          projection: am5map.geoMercator(),
          //wheelable: false
        })
      );
      
      // Países base
      this.polygonSeries = this.chart.series.push(
        am5map.MapPolygonSeries.new(root, {
          geoJSON: am5geodata_worldLow,
          exclude: ['AQ'],
          geodataNames: geoNames
        })
      );
      this.polygonSeries.mapPolygons.template.setAll({
        fill: am5.color(0xAAAAAA),
        interactive: true,
        tooltipText: "{name}",
        templateField: 'polygonSettings'
      });
      this.polygonSeries.mapPolygons.template.states.create('hover', {
        fill: am5.color(0xE66B23)
      });
      
      this.polygonSeries.data.setAll(highlightSeries);

      this.lineSeries = this.chart.series.push(am5map.MapLineSeries.new(root, {}));
      this.lineSeries.mapLines.template.setAll({
        stroke: root.interfaceColors.get("alternativeBackground"),
        strokeOpacity: 0,
      });

      this.animatedLineSeries = this.chart.series.push(am5map.MapLineSeries.new(root, {
      }));
      this.animatedLineSeries.mapLines.template.setAll({  
        stroke: am5.color(0xE66B23),
        strokeOpacity: 0.6,
        strokeWidth: 1,
       // strokeDasharray: [12, 16],
      });

      this.animatedBulletSeries = this.chart.series.push(am5map.MapPointSeries.new(root, {}));
      this.animatedBulletSeries.bullets.push(() =>
        am5.Bullet.new(root, { sprite: am5.Circle.new(root, { radius: 0 }) })
      );

      this.countryCentersSeries = this.chart.series.push(
        am5map.MapPointSeries.new(root, {
          idField: "id",
          polygonIdField: "id",
          calculateAggregates: true,
          valueField: "value",
          longitudeField: "longitude",
          latitudeField: "latitude"
        })
      );   

      this.countryCentersSeries.bullets.push((_root) => {
        const container = am5.Container.new(root, {
          cursorOverStyle:"pointer",
        }); 

        const circle = container.children.push(am5.Circle.new(root, {
          radius: 6,
          //tooltipText: "{title}",
          tooltipY: 0,
          fill: am5.color(0xE66B23),
          stroke: root.interfaceColors.get("background"),
          strokeWidth: 2
        }));  
        container.children.push(am5.Circle.new(root, {
          radius: 12,
          fillOpacity: 0.3,
          tooltipY: 0,
          fill: am5.color(0xff8c00)
        }));         
        container.children.push(am5.Circle.new(root, {
          radius: 16,
          fillOpacity: 0.3,
          tooltipY: 0,
          fill: am5.color(0xff8c00)
        }));      

        const labelBg = am5.RoundedRectangle.new(root, {
          fill: am5.color(0x000000),
          fillOpacity: 0.75,
          cornerRadiusTL: 8, cornerRadiusTR: 8, cornerRadiusBR: 8, cornerRadiusBL: 8
        });

        const label = am5.Label.new(root, {
          text: "{title}",          // usa o title do dataContext
          populateText: true,
          background: labelBg,
          paddingLeft: 8, paddingRight: 8, paddingTop: 4, paddingBottom: 4,
          centerX: am5.p0, centerY: am5.p50, // âncora do label
          dx: 14, dy: 14,
          visible: true,
          opacity: 0,
          fill: am5.color(0xFFFFFF),
          fontSize: 12
        });
        container.children.push(label);     

        (container as any).setPrivate("label", label);    

        circle.events.on('click', (ev) => {
          const bulletSprite = ev.target as unknown as am5.Container;
          const di = (bulletSprite.dataItem ??
                    (bulletSprite as any).dataItem ??
                    circle.dataItem) as am5.DataItem<am5map.IMapPointSeriesDataItem>;
          if (!di) return;

          const ctx: any = di.dataContext; // { id, name, ... }
          const id = ctx?.id as string | undefined;

          const myLabel = (container as any).getPrivate("label") as am5.Label | undefined;
          if (myLabel) {
            if (this.activeLabel && this.activeLabel !== myLabel) {
              this.activeLabel.setAll({ visible: false, opacity: 0 });
            }
            myLabel.setAll({ visible: true, opacity: 0 });
            myLabel.animate({ key: "opacity", from: 0, to: 1, duration: 200 });
            this.activeLabel = myLabel;
          }

          // tente zoom ao polígono do país
          if (id) {
            const polyDI = this.polygonSeries.getDataItemById(id);
            if (polyDI) {
              this.polygonSeries.zoomToDataItem(polyDI);
              return;
            }
          }

          // fallback: zoom pelo ponto (lon/lat)
          const lon = di.get('longitude') as number | undefined;
          const lat = di.get('latitude') as number | undefined;
          if (lon != null && lat != null) {
            this.chart.zoomToGeoPoint({ longitude: lon, latitude: lat }, 2.5);
          }
        });

        return am5.Bullet.new(root, {
          sprite: container
        });
      });  
      
      const colors = am5.ColorSet.new(root, { step: 2 });       

      this.countryCentersSeries.data.setAll(
        this.countries.map((c,i) => ({ ...c, id: c.id, polygonSettings: c.polygonSettings, colors: colors.getIndex(i) }))
      );

      this.polygonSeries.events.on('datavalidated', () => {
        this.originCountryId = this.pickRandomCountry();
        this.updateOriginAndRedraw();

        // Atualiza a origem a cada 60 segundos
        this.originInterval = setInterval(() => {
          //this.updateOriginAndRedraw();
        }, 60_000);     
      });

      this.chart.appear(1000, 100);
    });
  }

  private updateOriginAndRedraw() {
    // escolhe novo país
    const newOriginId = 'UY';//this.pickRandomCountry() ?? 'UY';
    this.originCountryId = newOriginId;

    // limpa séries antigas
    this.lineSeries.data.clear();
    this.animatedLineSeries.data.clear();
    this.animatedBulletSeries.data.clear();

    // redesenha conexões
    const originDI = this.countryCentersSeries.getDataItemById(this.originCountryId) as am5.DataItem<am5map.IMapPointSeriesDataItem> | null;
    if (!originDI) return;

    const lon0 = originDI.get('longitude') as number | undefined;
    const lat0 = originDI.get('latitude') as number | undefined;
    if (lon0 == null || lat0 == null) return;

    const destinationIds = this.countries.map(c => c.id).filter(id => id !== this.originCountryId);

    const shuffled = this.shuffleArray(destinationIds);

    shuffled.forEach((destId, idx) => {
      const destDI = this.countryCentersSeries.getDataItemById(destId) as am5.DataItem<am5map.IMapPointSeriesDataItem> | null;
      if (!destDI) return;

      const lon1 = destDI.get('longitude') as number | undefined;
      const lat1 = destDI.get('latitude') as number | undefined;
      if (lon1 == null || lat1 == null) return;

      const lineDI = this.lineSeries.pushDataItem({});
      lineDI.set('pointsToConnect', [originDI, destDI]);

      const startDI = this.animatedBulletSeries.pushDataItem({});
      startDI.setAll({ lineDataItem: lineDI, positionOnLine: 0 });

      const endDI = this.animatedBulletSeries.pushDataItem({});
      endDI.setAll({ lineDataItem: lineDI, positionOnLine: 1 });

      const animLineDI = this.animatedLineSeries.pushDataItem({});
      animLineDI.set('pointsToConnect', [startDI, endDI]);

      // duração proporcional à distância
      const distance = Math.hypot(lon1 - lon0, lat1 - lat0);
      const duration = Math.max(300, distance * 32);

      const delay = Math.floor(Math.random() * 600) + idx * 60;

      setTimeout(() => {
        // opcional: 50% começam "ao contrário" para variar ainda mais
        if (Math.random() < 0.5) {
          this.animateStart(startDI, endDI, duration);
        } else {
          this.animateEnd(startDI, endDI, duration);
        }
      }, delay);
    });
  }


  private animateStart(startDataItem: any, endDataItem: any, duration: number) {
    const startAnimation = startDataItem.animate({
      key: 'positionOnLine',
      from: 0,
      to: 1,
      duration
    });
    startAnimation.events.on('stopped', () => {
      this.animateEnd(startDataItem, endDataItem, duration);
    });
  }

  private animateEnd(startDataItem: any, endDataItem: any, duration: number) {
    startDataItem.set('positionOnLine', 0);
    const endAnimation = endDataItem.animate({
      key: 'positionOnLine',
      from: 0,
      to: 1,
      duration
    });
    endAnimation.events.on('stopped', () => {
      this.animateStart(startDataItem, endDataItem, duration);
    });
  }

  private shuffleArray<T>(arr: T[]): T[] {
    const a = [...arr];
    for (let i = a.length - 1; i > 0; i--) {
      const j = Math.floor(Math.random() * (i + 1));
      [a[i], a[j]] = [a[j], a[i]];
    }
    return a;
  }

  private pickRandomCountry(): string {
    const validCountries = this.countries.filter(c => c.longitude && c.latitude);
    const random = validCountries[Math.floor(Math.random() * validCountries.length)];
    return random.id;
  }



  ngOnDestroy() {
    if (this.originInterval) {
      clearInterval(this.originInterval);
    }    
    this.browserOnly(() => {
      if (this.root) {
        this.root.dispose();
      }
    });
  }    

  routeToPage(path?: string) {
    const translatedPath = this.localize.translateRoute(`${path}`);
    this.router.navigate([translatedPath]);
  }   
}
