#!/usr/bin/python3
####################################################################################
#
#  Copyright (c) 2018 Thanasis Vergoulis & Konstantinos Zagganas &  Loukas Kavouras
#  for the Information Management Systems Institute, "Athena" Research Center.
#  
#  This file is part of SCHeMa.
#  
#  SCHeMa is free software: you can redistribute it and/or modify
#  it under the terms of the GNU General Public License as published by
#  the Free Software Foundation, either version 3 of the License, or
#  (at your option) any later version.
#  
#  SCHeMa is distributed in the hope that it will be useful,
#  but WITHOUT ANY WARRANTY; without even the implied warranty of
#  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#  GNU General Public License for more details.
#  
#  You should have received a copy of the GNU General Public License
#  along with Foobar.  If not, see <https://www.gnu.org/licenses/>.
#
####################################################################################
from sklearn.datasets import make_classification
from sklearn.model_selection import train_test_split
from sklearn.linear_model import LogisticRegression
from sklearn.svm import SVC
from sklearn.ensemble import RandomForestClassifier
from sklearn.model_selection import GridSearchCV
from sklearn.metrics import classification_report
from sklearn import preprocessing
from sklearn.preprocessing import MinMaxScaler
from sklearn import svm
from sklearn.feature_selection import VarianceThreshold
from sklearn.feature_selection import SelectKBest
from sklearn.feature_selection import chi2

import csv
import sys
import math
import numpy as np
import pickle
import subprocess
import json
import time
import os.path
import psycopg2 as psg

# Define CSV dialect to be used.
csv.register_dialect(
    'my_dialect',
    delimiter = '|'
)



# === CONFIGURATION ===
kfold=10
test_ratio=0.2


#==== JOB CONFIGURATION

jobConfFileName=sys.argv[1];
jobConfFile=open(jobConfFileName,'r')
jobConf=json.load(jobConfFile);
jobConfFile.close()

profId=jobConf['id']
folder=jobConf['folder']
samplesFile=folder + '/final-' + profId + '.txt'
mem_limit=jobConf['memLimit']
# mem_limit=8


# Wait in a loop until the profiler script is finished

command="ps aux | grep 'profiler' | grep '" + profId + "' | grep -v 'grep'"
print('Profiler running. Waiting.')
try:
    out=subprocess.check_output(command,stderr=subprocess.STDOUT,shell=True, encoding='utf-8')
    out=out.split('\n')
except subprocess.CalledProcessError as exc:
    out=[]

while(len(out)>0):
    time.sleep(300)
    try:
        out=subprocess.check_output(command,stderr=subprocess.STDOUT,shell=True, encoding='utf-8')
        out=out.split('\n')
    except subprocess.CalledProcessError as exc:
        out=[]
print('Profiler finished. Starting classification.')

# === DATA LOADING & PREPROCESSING ===

# construct classifier input from file
try:
    bsamples_file = open(samplesFile,"r",encoding="utf-8") 
    bsamples_it = csv.reader(bsamples_file,dialect="my_dialect")
    has_header = csv.Sniffer().has_header(bsamples_file.read(1024))
    bsamples_file.seek(0)  # Rewind.
    reader = csv.reader(bsamples_file)
    if has_header:
        next(reader)

    print("=> Reading samples file ("+bsamples_file.name+")")
    bsamples_read = 0
    for bsample in bsamples_it:
        if len(bsample)!=0: #avoid empty lines (separators between different benchmarks)
            bsamples_read += 1
            if bsamples_read == 1:
                X_raw=[]
                X_raw_element=[]
                for i in range (1, len(bsample)-1):
                    if(bsample[i].isnumeric()):
                        X_raw_element.append(int(bsample[i]))
                X_raw.append(X_raw_element)
                if (float(bsample[-1])>mem_limit):
                    y_raw=[1]
                else:
                    y_raw=[0]
            else:
                X_raw_element=[]
                for i in range (1, len(bsample)-1):
                    if(bsample[i].isnumeric()):
                        X_raw_element.append(int(bsample[i]))
                X_raw.append(X_raw_element)
                if (float(bsample[-1])>mem_limit):
                    y_raw.append(1)
                else:
                    y_raw.append(0)
    print("\t- Input samples read: "+str(bsamples_read))
    

   

except IOError as e:
    print("=> ERROR: Cannot open file...")
    print(e) 

# transform arrays in numpy format
print("=> Transforming arrays in numpy format...")
X = np.array(X_raw)
y = np.array(y_raw)
print("\t- Done.")

# calculate normalization numbers to be used in novel predictions
xmin=X.min(axis=0)
xmax=X.max(axis=0)
scalerData={'min':xmin,'max':xmax}
filename = folder + '/scaler-' + profId + '.pkl'
g=open(filename,'wb')
pickle.dump(scalerData, g)
g.close()

# input features normalization
print("=> Normalizing inputs...")
min_max_scaler = preprocessing.MinMaxScaler()
X = min_max_scaler.fit_transform(X)
# X= SelectKBest(chi2, k=1).fit_transform(X, y)
print("\t- Done.")
print(X)

# data set split 
print("=> Splitting dataset...")
trainX, testX, trainy, testy = train_test_split(X, y, test_size=test_ratio, stratify=y, random_state=156) #stratification = split the dataset randomly but maintaining the class distribution in each subset
print("\t- Done.")

# selecting classification method

class_approaches=["log_regression", "log_regression_cs","svm","random_forest", "random_forest_cs"]


# # grid search for optimal hyperparameters

clfs=[None]*len(class_approaches)
best_params=[None]*len(class_approaches)
best_scores=[None]*len(class_approaches)
i=0
for class_approach in class_approaches:
    if class_approach == "log_regression" or class_approach == "log_regression_cs": # logistic regression / logistic regression cost sensitive
        # params for cross-validation
        tuned_parameters = {
            "max_iter": [2000,3000,10000],
            "solver": ['newton-cg', 'lbfgs', 'liblinear', 'sag', 'saga']
        }
        # determine classifier
        if class_approach == "log_regression":
            classifier = LogisticRegression()
        else:
            classifier = LogisticRegression(class_weight='balanced')
    
    elif class_approach == "svm": # SVM
        # params for cross-validation
        tuned_parameters = {
            'kernel': ['rbf', 'sigmoid'],
            'gamma': [1e-2, 1e-3, 1e-4, 1e-5],
            'C': [0.01, 0.1, 1, 10, 25, 50], 
            #'max_iter': [3000]
        } 
        # determine classifier
        classifier = SVC()
    elif class_approach == "random_forest" or class_approach == "random_forest_cs": #random forsst / random forest cost sensitive
        # Set the parameters by cross-validation
        tuned_parameters = {
                'max_depth': [1,5,10],
                'n_estimators': [100,150,200],
                'criterion': ['gini', 'entropy'],
                'max_features': ['log2', 'sqrt'],  # auto is equal to sqrt
        }
   
        if class_approach == "random_forest":
            classifier = RandomForestClassifier()
        else:
            classifier = RandomForestClassifier(class_weight='balanced')
            
    # for score in scores:
    print("=> Tuning hyper-paramaters for ", 'f1', "in", class_approach)
    clf = GridSearchCV( classifier, tuned_parameters, cv=kfold, scoring='%s' % 'f1',)
    print("=> Current Model", classifier)
    clf.fit(trainX, trainy)
    print("\t- Done.")
    print("=> Best parameters set found on development set:")
    print(clf.best_params_)
    
    clfs[i]=clf
    best_params[i]=clf.best_params_
    best_scores[i]=clf.best_score_

    predictions=clf.predict(testX)
    i=i+1
    
for row_index, (input, prediction, label) in enumerate(zip (testX, predictions, testy)):
  if prediction != label:
    print('Row', row_index, 'has been classified as ', prediction, 'and should be ', label)

best_score=0
best_clf=''
for i in range(len(best_scores)):
    if (best_scores[i]>=best_score):
        best_score=best_scores[i]
        best_clf=clfs[i]
        
filename = folder + '/model-' + profId + '.pkl'
g=open(filename,'wb')
pickle.dump(best_clf, g)
g.close()

configFileName=os.path.dirname(os.path.abspath(__file__)) + '/configuration.json'
configFile=open(configFileName,'r')
config=json.load(configFile)
configFile.close()

db=config['database']
host=db['host']
dbuser=db['username']
passwd=db['password']
dbname=db['database']

sql="UPDATE software SET profiled='t' where name='" + jobConf['name'] + "' AND version='" + jobConf['version'] + "'"

conn=psg.connect(host=host, user=dbuser, password=passwd, dbname=dbname)
cur=conn.cursor()
cur.execute(sql)
conn.commit()
conn.close()


