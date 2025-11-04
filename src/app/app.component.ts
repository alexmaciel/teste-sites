import { ChangeDetectionStrategy, Component, OnDestroy, OnInit } from '@angular/core';
import { NavigationCancel, NavigationEnd, Router, RouterOutlet } from '@angular/router';
import { registerLocaleData } from '@angular/common';
import { Subscription } from 'rxjs';

// 3rd-Party plugins variables
import { TranslateModule } from '@ngx-translate/core';
import { AnalyticsService, ApiExtendedService, TranslationService } from './core';

import localePt from '@angular/common/locales/pt';
import localeEn from '@angular/common/locales/en';
import localeEs from '@angular/common/locales/es';
// local
registerLocaleData(localePt, 'pt');
registerLocaleData(localeEn, 'en');
registerLocaleData(localeEs, 'es');

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [
    RouterOutlet,
    TranslateModule
  ],
  templateUrl: './app.component.html',
  styleUrl: './app.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AppComponent implements OnInit, OnDestroy {
  title = 'metabix';

  private unsubscribe: Subscription[] = [];

  constructor(
    private router: Router,
    // Services
    //private translation: TranslationService,
    private ApiExtended: ApiExtendedService,
    private analytics: AnalyticsService,
  ) { }

  ngOnInit() {
    const routerSubscription = this.router.events.subscribe((event) => {
      if (event instanceof NavigationEnd || event instanceof NavigationCancel) {
        // Trick the Router into believing it's last link wasn't previously loaded
        this.router.navigated = false;
        // clear filtration paginations and others
        this.ApiExtended.setDefaults();        
        // hide splash screen
        //this.splashService.hide();
        // analytics track
        this.analytics.trackPageViews();
        // scroll to top on every route change
        if (typeof document !== 'undefined') {
          // to display back the body content
          setTimeout(() => {
            document.body.classList.add('page-loaded');
            document.body.scrollTop = 0; // || window.scrollTo(0, 0);
          }, 100);     
        }     
        }
    }); 
    this.unsubscribe.push(routerSubscription);  
  }

  routerOutletActivation(active: boolean) {
    //console.log('routerOutletActivation', active);
  }  

  ngOnDestroy() {
    this.unsubscribe.forEach((routerSubscription) => routerSubscription.unsubscribe());
  }
     
}
