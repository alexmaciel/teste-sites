import { Component, OnDestroy, OnInit } from '@angular/core';
import { NavigationEnd, Router } from '@angular/router';
import { Observable, Subscription } from 'rxjs';

import { LocalizeRouterService } from '@gilsdav/ngx-translate-router';
import { 
  SocialService,
  SettingService,
  Social,
  Settings,
  // Translate
  LanguageService,
  TranslationService
} from '../../../core';

@Component({
  selector: 'app-aside',
  templateUrl: './aside.component.html',
  styleUrls: ['./aside.component.scss'],
})
export class AsideComponent implements OnInit, OnDestroy {

  settings$!: Observable<Settings>;
  social: Social[] = [];
  
  currentLang = '';

  private unsubscribe: Subscription[] = [];
  
  constructor(
    private router: Router,   
    private localize: LocalizeRouterService,
    private translation: TranslationService,
    // Services
    public settings: SettingService, 
    public socialService: SocialService,
    public languageService: LanguageService
  ) { 
    this.currentLang  = this.translation.getSelectedLanguage();
  }

  ngOnInit(): void {
    this.loadSocial();
    this.loadLanguages();
  }

  loadSocial() {
    const sb = this.socialService.getSocial().subscribe();
    this.unsubscribe.push(sb) 
  }

  loadLanguages() {
    const sb = this.languageService.getLanguages().pipe(
    ).subscribe();
    this.unsubscribe.push(sb);   
  } 

  switchLang(lang: string) {
    this.translation.setLanguage(lang); 
    const sb = this.router.events.subscribe((event: any) => {
      if (event instanceof NavigationEnd) {    
        this.currentLang  = this.translation.getSelectedLanguage();
      }
    });
    this.unsubscribe.push(sb);    
  }    

  ngOnDestroy() {
    this.unsubscribe.forEach((sb) => sb.unsubscribe());
  }

  calculateMenuItemCssClass(url: string): string {
    const path: any = this.localize.translateRoute(url)
    return checkIsActive(this.router.url, path) ? 'active' : '';
  }  
}

const getCurrentUrl = (pathname: string): string => {
  return pathname.split(/[?#]/)[0];
};

const checkIsActive = (pathname: string, url: string) => {
  const current = getCurrentUrl(pathname);
  if (!current || !url) {
    return false;
  }
  if (current === url) {
    return true;
  }
  if (current.indexOf(url) > -1) {
    return true;
  }
  return false;
};