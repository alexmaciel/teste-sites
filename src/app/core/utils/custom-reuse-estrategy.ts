import {ActivatedRouteSnapshot, BaseRouteReuseStrategy, DetachedRouteHandle, RouteReuseStrategy} from '@angular/router';

export class CustomReuseStrategy extends BaseRouteReuseStrategy implements RouteReuseStrategy {

  private storedHandles = new Map<string, DetachedRouteHandle>();
  
  override shouldDetach(route: ActivatedRouteSnapshot): boolean {
    return !!route.data['reuseComponent'];
  }

  override store(route: ActivatedRouteSnapshot, handle: DetachedRouteHandle | null): void {
    if (handle) {
      const key = this.getRouteKey(route);
      this.storedHandles.set(key, handle);
    }
  }

  override shouldAttach(route: ActivatedRouteSnapshot): boolean {
    const key = this.getRouteKey(route);
    return this.storedHandles.has(key);
  }

  override retrieve(route: ActivatedRouteSnapshot): DetachedRouteHandle | null {
    const key = this.getRouteKey(route);
    return this.storedHandles.get(key) || null;
  }

  override shouldReuseRoute(future: ActivatedRouteSnapshot, curr: ActivatedRouteSnapshot): boolean {
    return future.routeConfig === curr.routeConfig && !!future.data['reuseComponent'];
  }

  private getRouteKey(route: ActivatedRouteSnapshot): string {
    return route.pathFromRoot.map(r => r.url.map(u => u.toString()).join('/')).join('/');
  }  
}