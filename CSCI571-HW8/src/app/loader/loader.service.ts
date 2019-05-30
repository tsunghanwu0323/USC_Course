import { Injectable, OnDestroy } from '@angular/core';
import { Subject,  } from 'rxjs';
import { LoaderState } from './loader';
import { debounceTime } from 'rxjs/operators';

@Injectable()
export class LoaderService implements OnDestroy {
  readonly progress$: Subject<number> = debounceTime.call(
    new Subject<number>()
  );
  private _value = 0;
  private _pendingRequests = 0;
  private _timeOut: any;

  private loaderSubject = new Subject<LoaderState>();
  loaderState = this.loaderSubject.asObservable();

  constructor() {}

  show() {
    this._pendingRequests += 1;
    this.loaderSubject.next({ show: true } as LoaderState);
    if (!this._value || this._pendingRequests === 1) {
      this.set(
        this._pendingRequests === 1 && this._value > 0 ? this._value : 0
      );
    }
  }

  set(value) {
    this._value = value;
    this.progress$.next(value);
    if (!this._pendingRequests) {
      return;
    }
    clearTimeout(this._timeOut);
    if (this._value >= 0 && this._value < 95) {
      this._timeOut = setTimeout(() => this.set(this._value + 5), 100);
    }
  }

  hide() {
    if (!this._pendingRequests && !this._value) {
      return;
    }
    if (this._pendingRequests > 0) {
      this._pendingRequests -= 1;
    }
    if (!this._pendingRequests) {
      this.set(100);
    }
    this.loaderSubject.next({ show: false } as LoaderState);
    setTimeout(() => this.set(0), 500);
  }

  ngOnDestroy() {
    this.progress$.complete();
  }
}
