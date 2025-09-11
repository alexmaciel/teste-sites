import { Component, Input, OnInit, OnDestroy } from '@angular/core';
import { Router } from '@angular/router';
import { Subscription } from 'rxjs';

import { LocalizeRouterService } from '@gilsdav/ngx-translate-router';

import { 
  SocialService,
  SettingService,
} from '../../../core';

@Component({
  selector: 'app-footer',
  templateUrl: './footer.component.html'
})
export class FooterComponent implements OnInit, OnDestroy {
  @Input() appFooterContainer?: 'fixed' | 'fluid';
  @Input() appFooterContainerCSSClass = '';

  private unsubscribe: Subscription[] = [];

  constructor(
    private router: Router,
    private localize: LocalizeRouterService,
    // Services
    public settings: SettingService,  
    public socials: SocialService
  ) {}  

  ngOnInit(): void {
    this.loadSocial();
  }
  
  loadSocial() {
    const sb = this.socials.getSocial().subscribe();
    this.unsubscribe.push(sb)     
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